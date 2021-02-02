#include "sap2ism.h"

char *TrimWhiteSpace(char *str)
{
	char *end = NULL;

	check(str != NULL, ERROR_STR_INVALIDINPUT);
	while(isspace(*str)) str++;
	if(*str == 0)
		return str;
	end = str + strlen(str) - 1;
	while(end > str && isspace(*end)) end--;
	*(end + 1) = 0;
	return str;
error:
	return NULL;
}

char *CleanEscapeXmlContent(MYSQL *mysqlHandle, xmlNode *textNode)
{
	xmlChar *textNodeVal = NULL;
	char *escapedText = NULL;
	char *trimmedText = NULL;

	check(textNode != NULL && mysqlHandle != NULL, ERROR_STR_INVALIDINPUT);
	check_debug(textNode->type == XML_TEXT_NODE || textNode->type == XML_CDATA_SECTION_NODE, ERROR_STR_INVALIDINPUT);

	textNodeVal = xmlNodeGetContent(textNode);
	check(textNodeVal != NULL, "Cannot get xml node value.");
	trimmedText = TrimWhiteSpace((char *)textNodeVal);
	check(trimmedText != NULL, "Cannot trim whitespaces.");
	size_t len = strlen(trimmedText);
	escapedText = calloc(len * 2 + 1, 1);
	check_mem(escapedText);
	check(mysql_real_escape_string(mysqlHandle, escapedText, trimmedText, len) != 0, "Cannot escape text.");
	xmlFree(textNodeVal);
	return escapedText;
error:
	if (textNodeVal != NULL)
		xmlFree(textNodeVal);
	if (escapedText != NULL)
		free(escapedText);
	return NULL;
}

TQueryList *ParseValidity(MYSQL *mysqlHandle, xmlNode *node, xmlChar *id)
{
	TQueryList *queries = NULL;
	char *valId = NULL, *validityInsert = NULL;
	
	check(id != NULL && mysqlHandle != NULL && node != NULL, ERROR_STR_INVALIDINPUT);
	for (; node != NULL; node = node->next)
	{
		if (node->type != XML_ELEMENT_NODE)
			continue;
		int valType = 0;
		if (!xmlStrcmp(node->name, BAD_CAST "change"))
			valType = 1;
		else if (!xmlStrcmp(node->name, BAD_CAST "cancel"))
			valType = 2;
		valId = CleanEscapeXmlContent(mysqlHandle, node->xmlChildrenNode);
		check(valId != NULL, "Error while escaping validity id.");
		validityInsert = calloc(strlen(SQL_QUERY_INSERTVALIDITY)+2*strlen((char *)id) + 3, 1);
		check_mem(validityInsert);
		check(sprintf(validityInsert, SQL_QUERY_INSERTVALIDITY, valId, id, valType) > 0, "Cannot compose insert validity query.");
		check(QueryListAdd(&queries, validityInsert) == true, "Cannot compose validity query list.");
		free(validityInsert);
		free(valId);
		validityInsert = NULL;
		valId = NULL;
	}
	return queries;
error:
	if (queries != NULL)
		QueryListFree(queries);
	if (valId != NULL)
		free(valId);
	if (validityInsert != NULL)
		free(validityInsert);
	return NULL;
}

TQueryList *ParseFileLists(MYSQL *mysqlHandle, xmlNode *node, xmlChar *id)
{
	TQueryList *queries = NULL;
	xmlNode *curChld = NULL;
	char *fileDesc = NULL, *filesInsert = NULL, *fileName = NULL;
	char *dotPosition = NULL;
	size_t fileNameLen = 0, fileExtLen = 0, fileIdLen = 0;

	check(id != NULL && mysqlHandle != NULL && node != NULL, ERROR_STR_INVALIDINPUT);
	size_t docIdLen = strlen((char *)id);
	for (; node != NULL; node = node->next)
	{
		if (node->type != XML_ELEMENT_NODE)
			continue;
		for (curChld = node->children; curChld != NULL; curChld = curChld->next)
		{
			if (curChld->type != XML_ELEMENT_NODE)
				continue;
			if (!xmlStrcmp(curChld->name, BAD_CAST "file_description"))
			{
					fileDesc = CleanEscapeXmlContent(mysqlHandle, curChld->xmlChildrenNode);
					check(fileDesc != NULL, "Error while escaping fileDesc.");
			}
			else if (!xmlStrcmp(curChld->name, BAD_CAST "file_name"))
			{
				fileName = CleanEscapeXmlContent(mysqlHandle, curChld->xmlChildrenNode);
				check(fileName != NULL, "Error while escaping fileName.");
				fileNameLen = strlen(fileName);
				dotPosition = strchr(fileName, '.');
				fileIdLen = fileNameLen - docIdLen;
				if (dotPosition != NULL)
				{
					dotPosition++;
					fileIdLen--;
				}				
				else
					dotPosition = fileName + fileNameLen;
				fileExtLen = strlen(dotPosition);
				fileIdLen -= fileExtLen;
				check(strncmp(fileName, (char *)id, docIdLen) == 0, "Filename and document id does not match.");
			}
		}
		check(fileDesc != NULL && fileName != NULL, "Cannot parse file_list node.");
		filesInsert = calloc(strlen(SQL_QUERY_INSERTFILELIST) + docIdLen + fileIdLen + fileExtLen + strlen(fileDesc) + 1, 1);
		check_mem(filesInsert);
		check(sprintf(filesInsert, SQL_QUERY_INSERTFILELIST, id, (int)fileIdLen, fileName + docIdLen, dotPosition, fileDesc) > 0, "Cannot compose insert filelist query.");
		check(QueryListAdd(&queries, filesInsert) == true, "Cannot compose filelist query list.");
		free(fileDesc);
		free(fileName);
		free(filesInsert);
		fileDesc = NULL;
		fileName = NULL;
		filesInsert = NULL;
	}
	return queries;
error:
	if (queries != NULL)
		QueryListFree(queries);
	if (fileDesc != NULL)
		free(fileDesc);
	if (filesInsert != NULL)
		free(filesInsert);
	if (fileName != NULL)
		free(fileName);
	return NULL;
}

TQueryList *ParseXmlFile(MYSQL *mysqlHandle, const char *xmlFile)
{
	xmlDoc *doc = NULL;
	xmlNode *docNode = NULL, *docChld = NULL;
	char *documentInsert = NULL, *documentDelete = NULL,  *type = NULL, *number = NULL, *author = NULL, *date = NULL, *description = NULL, *department = NULL;
	xmlChar *id = NULL;
	TQueryList *queries = NULL, *validities = NULL, *files = NULL;

	check(xmlFile != NULL && mysqlHandle != NULL, ERROR_STR_INVALIDINPUT);
	doc = xmlReadFile(xmlFile, "UTF-8", XML_PARSE_NOBLANKS | XML_PARSE_NONET);
	check(doc != NULL, "Cannot parse xml file.");
	docNode = xmlDocGetRootElement(doc);
	check(docNode != NULL, "Cannot get xml root node.");
	for (docNode = docNode->children; docNode != NULL; docNode = docNode->next)
	{
		if (docNode->type != XML_ELEMENT_NODE)
			continue;
		id = xmlGetProp(docNode, BAD_CAST "id");
		check(id != NULL, "Cannot get id property.")
		for (docChld = docNode->children; docChld != NULL; docChld = docChld->next)
		{
			if (docChld->type != XML_ELEMENT_NODE)
				continue;
			if (!xmlStrcmp(docChld->name, BAD_CAST "type"))
			{
				type = CleanEscapeXmlContent(mysqlHandle, docChld->xmlChildrenNode);
				check(type != NULL, "Error while escaping type.")
			}
			else if (!xmlStrcmp(docChld->name, BAD_CAST "number"))
			{
				number = CleanEscapeXmlContent(mysqlHandle, docChld->xmlChildrenNode);
				check(number != NULL, "Error while escaping number.");
			}
			else if (!xmlStrcmp(docChld->name, BAD_CAST "author"))
			{
				author = CleanEscapeXmlContent(mysqlHandle, docChld->xmlChildrenNode);
				check(author != NULL, "Error while escaping author.");
			}
			else if (!xmlStrcmp(docChld->name, BAD_CAST "department"))
			{
				department = CleanEscapeXmlContent(mysqlHandle, docChld->xmlChildrenNode);
				check(department != NULL, "Error while escaping department.");
			}
			else if (!xmlStrcmp(docChld->name, BAD_CAST "date"))
			{
				date = CleanEscapeXmlContent(mysqlHandle, docChld->xmlChildrenNode);
				check(date != NULL, "Error while escaping author.");
			}
			else if (!xmlStrcmp(docChld->name, BAD_CAST "description"))
			{
				if (docChld->xmlChildrenNode != NULL)
					description = CleanEscapeXmlContent(mysqlHandle, docChld->xmlChildrenNode);
				if (description == NULL)
				{
					description = calloc(2, 1);
					check_mem(description);
				}
			}
			else if (!xmlStrcmp(docChld->name, BAD_CAST "validity"))
			{
				validities = ParseValidity(mysqlHandle, docChld->children, id);
				check(validities != NULL, "Error while parsing validity node.");
			}
			else if (!xmlStrcmp(docChld->name, BAD_CAST "file_list"))
			{
				files = ParseFileLists(mysqlHandle, docChld->children, id);
				check(files != NULL, "Error while parsing file_list node.");
			}
		}
		check(type != NULL && number != NULL && author != NULL && date != NULL && description != NULL && files != NULL, "Error while parsing document node.");
		if (department == NULL)
		{
			department = calloc(2, 1);
			check_mem(department);
			check(strncpy(department, "0", strlen("0")) != NULL, "String copy has failed.");
		}
		documentDelete = calloc(strlen(SQL_QUERY_DELETEDOCUMENT) + strlen((char *)id) + 1, 1);
		check_mem(documentDelete);
		check(sprintf(documentDelete, SQL_QUERY_DELETEDOCUMENT, id) > 0, "Cannot compose delete document query.");
		check(QueryListAdd(&queries, documentDelete) == true, "Cannot compose delete document query list.");
		documentInsert = calloc(strlen(SQL_QUERY_INSERTDOCUMENT) + strlen((char *)id) + strlen(type) + strlen(number) + strlen(author) + strlen(department) + strlen(date) + strlen(description) + 1, 1);
		check_mem(documentInsert);
		check(sprintf(documentInsert, SQL_QUERY_INSERTDOCUMENT, id, type, number, author, date, description, department) > 0, "Cannot compose insert document query.");
		check(QueryListAdd(&queries, documentInsert) == true, "Cannot compose insert document query list.");
		if (validities != NULL)
		{
			check(QueryListAppend(queries, validities) == true, "Cannot append validities query list.");
		}
		check(QueryListAppend(queries, files) == true, "Cannot append files query list.");
		xmlFree(id);
		free(type);
		free(number);
		free(author);
		free(date);
		free(description);
		free(documentInsert);
		free(documentDelete);
		free(department);
		department = NULL;
		documentInsert = NULL;
		documentDelete = NULL;
		id = NULL;
		type = NULL;
		number = NULL;
		author = NULL;
		date = NULL;
		description = NULL;
		validities = NULL;
		files = NULL;
	}
	xmlFreeDoc(doc);
	return queries;
error:
	QueryListFree(queries);
	QueryListFree(validities);
	QueryListFree(files);
	if (doc != NULL)
		xmlFreeDoc(doc);
	if (id != NULL)
		xmlFree(id);
	if (documentDelete != NULL)
		free(documentDelete);
	if (documentInsert != NULL)
		free(documentInsert);
	if (type != NULL)
		free(type);
	if (number != NULL)
		free(number);
	if (author != NULL)
		free(author);
	if (department != NULL)
		free(department);
	if (date != NULL)
		free(date);
	if (description != NULL)
		free(description);
	return NULL;
}
