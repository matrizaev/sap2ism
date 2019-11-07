#include "sap2ism.h"

bool ValidateXmlFile(const char *fileName)
{
	xmlDocPtr doc = NULL;
	xmlDocPtr schemaDoc = NULL;
	xmlSchemaParserCtxtPtr parserCtxt = NULL;
	xmlSchemaPtr schema = NULL;
	xmlSchemaValidCtxtPtr validCtxt = NULL;
	bool isValid = false;

	check(fileName != NULL, "validate_xml_file had invalid input parameters.");
	doc = xmlParseFile(fileName);
	check(doc != NULL, "Cannot parse file for validation.");
	schemaDoc = xmlReadFile(SCHEMA_FILENAME, NULL, XML_PARSE_NONET);
	check(schemaDoc != NULL, "Cannot read schema file.");
	parserCtxt = xmlSchemaNewDocParserCtxt(schemaDoc);
	check(parserCtxt != NULL, "Cannot create parser context.");
	schema = xmlSchemaParse(parserCtxt);
	check(schema != NULL, "Cannot create schema.");	
	validCtxt = xmlSchemaNewValidCtxt(schema);
	check(validCtxt != NULL, "Cannot create validation context.");		
	isValid = (xmlSchemaValidateDoc(validCtxt, doc) == 0);
error:
	if (validCtxt != NULL) 
		xmlSchemaFreeValidCtxt(validCtxt);
	if (schema != NULL)
		xmlSchemaFree(schema);
	if (parserCtxt != NULL)	
		xmlSchemaFreeParserCtxt(parserCtxt);
	if (schemaDoc != NULL)
		xmlFreeDoc(schemaDoc);
	if (doc != NULL)
		xmlFreeDoc(doc);
	return isValid;
}

void CloseMysqlConnection(MYSQL *mysqlHandle)
{
	if (mysqlHandle != NULL)
		mysql_close(mysqlHandle);
	return;
}

MYSQL *InitMysqlConnection()
{
	MYSQL *mysqlHandle = NULL;
	mysqlHandle = mysql_init(NULL);
	check(mysqlHandle != NULL, "MySQL initialization has failed.");
	check(mysql_real_connect(mysqlHandle, DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_SCHEMA, 3306, NULL, 0) != NULL, "MySQL connect failed.");
	check(mysql_set_character_set(mysqlHandle, "utf8") == 0, "MySQL set character set failed.");
	return mysqlHandle;
error:
	CloseMysqlConnection(mysqlHandle);
	return NULL;
}

int PerformMysqlQuery(MYSQL *mysqlHandle, TQueryList *queries)
{
	int queryResult = 0, querySum = 0;
	TQueryList *temp = NULL;

	check((queries != NULL) && (mysqlHandle != NULL), ERROR_STR_INVALIDINPUT);
	for (temp = queries; temp != NULL; temp = temp->next)
	{
		queryResult = mysql_query(mysqlHandle, temp->query);
		if (queryResult != 0)
			log_err("Error in query: %s\n\t%s", temp->query, mysql_error(mysqlHandle));
		querySum += queryResult;
	}
	return querySum;
error:
	return -1;
}

int MoveFile(const char *fileName, const char *directory)
{
	char *errorPath = NULL;
	struct stat sb;
	int result = -1;
	
	check((fileName != NULL) && (directory != NULL), ERROR_STR_INVALIDINPUT);
	check(access(fileName, F_OK) == 0, "Cannot access specified file.");
	check(stat(directory, &sb) == 0, "Cannot stat specified directory.");
	check(S_ISDIR(sb.st_mode), "Specified directory is not a directory.");
	check(access(directory, F_OK) == 0, "Cannot access specified directory.");
	errorPath = calloc(1, strlen(fileName) + strlen(directory) + 2);
	check_mem(errorPath);
	check(strcpy(errorPath, directory) != NULL, "Error while composing error directory path.");
	check(strcat(errorPath, fileName) != NULL, "Error while composing error directory path.");
	check(rename(fileName, errorPath) == 0, "Error while moving file to error directory.");
	log_info("Moving %s to %s directory.", fileName, directory);
	result = 0;
error:
	if (errorPath != NULL)
		free(errorPath);
	return result;
}

int FilterXmlFiles(const struct dirent *d)
{
	const char * xmlFilenameTemplate = "PR%2u%2u%2u%2u%.xml";
	int year = 0;
	int month = 0;
	int day = 0;
	int num = 0;
	const time_t epochTime = time(NULL);
	struct tm *t = NULL;

	check(d != NULL, ERROR_STR_INVALIDINPUT);
	t = localtime(&epochTime);
	check(t != NULL, "Local time is undefined.");
	if (strlen(d->d_name) == 14 && sscanf(d->d_name, xmlFilenameTemplate, &year, &month, &day, &num) == 4)
	{
		if (((t->tm_year - 100) == year) && ((t->tm_mon + 1) == month) && (t->tm_mday == day))
		{
			if (ValidateXmlFile(d->d_name) == true)
			{
				return 1;
			}
			else
				MoveFile(d->d_name, ERROR_DIRECTORY);
			
		}
	}
error:
	return 0;
}


int main(int argc, char *argv[])
{
	int result = -1;
	struct dirent **xmlFiles = NULL;
	int filesCount = 0;
	MYSQL *mysqlHandle = NULL;
	
	LIBXML_TEST_VERSION
	check(argc == 2, "Usage:\n\t%s working_directory", argv[0]);
	check(chdir(argv[1]) == 0, "Cannot set working directory.");
	check(mysql_library_init(0, NULL, NULL) == 0, "MySQL library initialization has failed.");
	xmlInitParser();
	mysqlHandle = InitMysqlConnection();
	check(mysqlHandle != NULL, "Connection to MySQL database has failed.");
	filesCount = scandir (argv[1], &xmlFiles, FilterXmlFiles, alphasort);
	check((xmlFiles != NULL) && (filesCount > 0), "No valid files were found.");
	for (int i = 0; i < filesCount; i++)
	{
		TQueryList *queries = ParseXmlFile(mysqlHandle, xmlFiles[i]->d_name);
		if (queries != NULL)
		{
			int queryResult = PerformMysqlQuery(mysqlHandle, queries);
			QueryListFree(queries);
			if (queryResult != 0)
				MoveFile(xmlFiles[i]->d_name, ERROR_DIRECTORY);
			else
				unlink(xmlFiles[i]->d_name);
		}
		else
		{
			log_err("Error parsing xml file %s.", xmlFiles[i]->d_name);
			MoveFile(xmlFiles[i]->d_name, ERROR_DIRECTORY);
		}
	}
	result = 0;
error:
	if (xmlFiles != NULL)
	{
		for (int i = 0; i < filesCount; i++)
			if (xmlFiles[i] != NULL)
				free(xmlFiles[i]);
		free(xmlFiles);
	}
	xmlCleanupParser();
	CloseMysqlConnection(mysqlHandle);	
	mysql_library_end();
	return result;
}
