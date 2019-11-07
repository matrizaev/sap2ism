#include "ism-sanity.h"

void CloseMysqlConnection(MYSQL *mysqlHandle)
{
	if (mysqlHandle != NULL)
		mysql_close(mysqlHandle);
	return;
}

MYSQL *InitMysqlConnection(const char* databaseHost, const char* databaseUser, const char* databasePassword, const char* databaseSchema)
{
	MYSQL *mysqlHandle = NULL;
	mysqlHandle = mysql_init(NULL);
	check(mysqlHandle != NULL, "MySQL initialization has failed.");
	check(mysql_real_connect(mysqlHandle, databaseHost, databaseUser, databasePassword, databaseSchema, 3306, NULL, 0) != NULL, "MySQL connect failed.");
	check(mysql_set_character_set(mysqlHandle, "utf8") == 0, "MySQL set character set failed.");
	return mysqlHandle;
error:
	CloseMysqlConnection(mysqlHandle);
	return NULL;
}

bool CleanDatabase(MYSQL *mysqlHandle, const char *listingQuery, bool (*CleanFunc)(MYSQL*, const char*))
{
	bool result = false;
	MYSQL_RES *mysqlQueryResult = NULL;
	MYSQL_ROW mysqlRow = NULL;
	int queryError = 0;
	
	check(mysqlHandle != NULL && listingQuery != NULL && CleanFunc != NULL, ERROR_STR_INVALIDINPUT);
	queryError = mysql_query(mysqlHandle, listingQuery);
	check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
	mysqlQueryResult = mysql_store_result(mysqlHandle);
	check(mysqlQueryResult != NULL, "Store queried data failed: %s", mysql_error(mysqlHandle));
	while((mysqlRow = mysql_fetch_row(mysqlQueryResult)))
	{
		check(CleanFunc(mysqlHandle, (char *) mysqlRow[0]) == true, "Clean database has failed.");
	}
	result = true;
error:
	mysql_free_result(mysqlQueryResult);
	return result;
}

bool CleanCategories(MYSQL *mysqlHandle, const char *pathElement)
{
	bool result = false;
	char *queryBuffer = NULL;
	int queryError = 0;
	MYSQL_RES *mysqlQueryResult = NULL;
	MYSQL_ROW mysqlRow = NULL;
	my_ulonglong mysqlNumRows = 0;
	char *pathElementNext = NULL;
	unsigned int pathLength = 0;
	bool badItem = false;
	
	check(mysqlHandle != NULL && pathElement != NULL, ERROR_STR_INVALIDINPUT);
	pathElementNext = strdup(pathElement);
	check_mem(pathElementNext);
	while ((badItem != true) && strncmp(pathElementNext, "-1", strlen("-1")) != 0)
	{
		badItem = true;
		queryBuffer = calloc(strlen(pathElementNext) + strlen(SQL_QUERY_GETPARENT) + 1, 1);
		check_mem(queryBuffer);
		check(sprintf(queryBuffer, SQL_QUERY_GETPARENT, pathElementNext) > 0, "Cannot construct query for path structure.");
		queryError = mysql_query(mysqlHandle, queryBuffer);
		check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
		mysqlQueryResult = mysql_store_result(mysqlHandle);	
		mysqlNumRows = mysql_num_rows(mysqlQueryResult);
		if (pathLength < 50 && mysqlNumRows > 0)
		{
			mysqlRow = mysql_fetch_row(mysqlQueryResult);
			if (strcmp(pathElementNext, (char *) mysqlRow[0]) != 0)
			{
				badItem = false;
				free(pathElementNext);
				pathElementNext = strdup((char *) mysqlRow[0]);
			}
		}
		free(queryBuffer);
		mysql_free_result(mysqlQueryResult);
		queryBuffer = NULL;
		mysqlQueryResult = NULL;
	}
	if (badItem == true)
	{
		queryBuffer = calloc(strlen(pathElement) + strlen(SQL_QUERY_DELETECAT) + 1, 1);
		check_mem(queryBuffer);
		check(sprintf(queryBuffer, SQL_QUERY_DELETECAT, pathElement) > 0, "Cannot construct query for delete category.");
		printf("Bad category was found %s. It will be deleted.\n", pathElement);
		queryError = mysql_query(mysqlHandle, queryBuffer);
		check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
	}
	result = true;
error:
	if (pathElementNext != NULL)
		free(pathElementNext);
	if (queryBuffer != NULL)
		free(queryBuffer);
	if (mysqlQueryResult != NULL)
		mysql_free_result(mysqlQueryResult);
	return result;
}

bool CleanLinksAttitude(MYSQL *mysqlHandle, const char *linkAttitude)
{
	bool result = false;
	char *queryBuffer = NULL;
	int queryError = 0;
	
	check((mysqlHandle != NULL && linkAttitude != NULL), ERROR_STR_INVALIDINPUT);
	queryBuffer = calloc(strlen(linkAttitude) + strlen(SQL_QUERY_DELETECL) + 1, 1);
	check_mem(queryBuffer);
	check(sprintf(queryBuffer, SQL_QUERY_DELETECL, linkAttitude) > 0, "Cannot construct query for delete linkAttitude.");
	printf("Bad attitude was found %s. It will be deleted.\n", linkAttitude);
	queryError = mysql_query(mysqlHandle, queryBuffer);
	check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
	result = true;
error:
	if (queryBuffer != NULL)
		free(queryBuffer);
	return result;
}

bool CleanLinks(MYSQL *mysqlHandle, const char *link)
{
	bool result = false;
	char *queryBuffer = NULL;
	int queryError = 0;
	
	check(mysqlHandle != NULL && link != NULL, ERROR_STR_INVALIDINPUT);
	queryBuffer = calloc(strlen(link) + strlen(SQL_QUERY_DELETELINK) + 1, 1);
	check_mem(queryBuffer);
	check(sprintf(queryBuffer, SQL_QUERY_DELETELINK, link) > 0, "Cannot construct query for delete link.");
	printf("Bad link was found %s. It will be deleted.\n", link);
	queryError = mysql_query(mysqlHandle, queryBuffer);
	check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
	result = true;
error:
	if (queryBuffer != NULL)
		free(queryBuffer);
	return result;
}

bool CleanLinksCf(MYSQL *mysqlHandle, const char *link)
{
	bool result = false;
	char *queryBuffer = NULL;
	int queryError = 0;
	
	check(mysqlHandle != NULL && link != NULL, ERROR_STR_INVALIDINPUT);
	queryBuffer = calloc(strlen(link) + strlen(SQL_QUERY_DELETECF) + 1, 1);
	check_mem(queryBuffer);
	check(sprintf(queryBuffer, SQL_QUERY_DELETECF, link) > 0, "Cannot construct query for delete link.");
	printf("Bad cf was found %s. It will be deleted.\n", link);
	queryError = mysql_query(mysqlHandle, queryBuffer);
	check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
	result = true;
error:
	if (queryBuffer != NULL)
		free(queryBuffer);
	return result;
}

bool CleanLinksCfAtt(MYSQL *mysqlHandle, const char *link)
{
	bool result = false;
	char *queryBuffer = NULL;
	int queryError = 0;
	
	check(mysqlHandle != NULL && link != NULL, ERROR_STR_INVALIDINPUT);
	queryBuffer = calloc(strlen(link) + strlen(SQL_QUERY_DELETECFATT) + 1, 1);
	check_mem(queryBuffer);
	check(sprintf(queryBuffer, SQL_QUERY_DELETECFATT, link) > 0, "Cannot construct query for delete link.");
	printf("Bad cf_att was found %s. It will be deleted.\n", link);
	queryError = mysql_query(mysqlHandle, queryBuffer);
	check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
	result = true;
error:
	if (queryBuffer != NULL)
		free(queryBuffer);
	return result;
}


bool CleanCategoriesAcl(MYSQL *mysqlHandle, const char *cat)
{
	bool result = false;
	char *queryBuffer = NULL;
	int queryError = 0;
	
	check(mysqlHandle != NULL && cat != NULL, ERROR_STR_INVALIDINPUT);
	queryBuffer = calloc(strlen(cat) + strlen(SQL_QUERY_DELETEACL) + 1, 1);
	check_mem(queryBuffer);
	check(sprintf(queryBuffer, SQL_QUERY_DELETEACL, cat) > 0, "Cannot construct query for delete acl.");
	printf("Bad acl was found %s. It will be deleted.\n", cat);
	queryError = mysql_query(mysqlHandle, queryBuffer);
	check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
	result = true;
error:
	if (queryBuffer != NULL)
		free(queryBuffer);
	return result;
}


bool CleanFileSystem(MYSQL *mysqlHandle, const char *fileName)
{
	bool result = false;
	char *queryBuffer = NULL;
	int queryError = 0;
	unsigned int fileLinkId = 0;
	char c = 0;
	MYSQL_RES *mysqlQueryResult = NULL;
	my_ulonglong mysqlNumRows = 0;
	
	check(mysqlHandle != NULL && fileName != NULL, ERROR_STR_INVALIDINPUT);
	if (sscanf(fileName, "24-%u%c", &fileLinkId, &c) == 2)
	{
		if (c == '.')
		{
			queryBuffer = calloc((int)log10(fileLinkId) + strlen(SQL_QUERY_CHECKFILENAME) + 2, 1);
			check_mem(queryBuffer);
			check(sprintf(queryBuffer, SQL_QUERY_CHECKFILENAME, fileLinkId) > 0, "Cannot construct query for check file name.");
			queryError = mysql_query(mysqlHandle, queryBuffer);
			check(queryError == 0, "Query to database failed: %s", mysql_error(mysqlHandle));
			mysqlQueryResult = mysql_store_result(mysqlHandle);	
			mysqlNumRows = mysql_num_rows(mysqlQueryResult);
			if (mysqlNumRows == 0)
			{
				printf("Bad fileName was found %s. It will be deleted.\n", fileName);
				check(unlink(fileName) == 0, "Cannot delete file %s.", fileName);
			}
		}
	}
	result = true;
error:
	if (queryBuffer != NULL)
		free(queryBuffer);
	if (mysqlQueryResult != NULL)
		mysql_free_result(mysqlQueryResult);
	return result;	
}

int FilterLinkFiles(const struct dirent *d)
{
	int result = 0;
	check(d != NULL, ERROR_STR_INVALIDINPUT)
	if (strlen(d->d_name) > strlen("24-"))
	{
		if (strncmp(d->d_name, "24-", strlen("24-")) == 0)
		{
			result = 1;
		}
	}
error:
	return result;
}

int main(int argc, char *argv[])
{
	int result = -1;
	MYSQL *mysqlHandle = NULL;
	struct dirent **linkFiles = NULL;
	int filesCount = 0;
	int nextOption = 0;
	const char* workingDirectory = NULL;
	const char* programName = NULL;
	const char* databaseHost = NULL;
	const char* databaseUser = NULL;
	const char* databasePassword = NULL;
	const char* databaseSchema = NULL;
	const char* const shortOptions = "hH:s:u:p:";
	const struct option longOptions[] =
		{{ "help",		0, NULL, 'h' },
		{ "host",		1, NULL, 'H' },
		{ "schema",		1, NULL, 's' },
		{ "user",		1, NULL, 'u' },
		{ "password",	1, NULL, 'p' },
		{ NULL,			0, NULL, 0   }};
	
	programName = argv[0];
	do
	{
		nextOption = getopt_long (argc, argv, shortOptions, longOptions, NULL);
		switch (nextOption)
		{
			case 'h':
				printf("Usage:\n\t%s -H <database_host> -s <database_schema> -u <database_user> -p <database_password> <working_directory>", programName);

			case 'H':
				databaseHost = optarg;
				break;

			case 's':
				databaseSchema = optarg;
				break;

			case 'u':
				databaseUser = optarg;
				break;
			  
			case 'p':
				databasePassword = optarg;
				break;

			case -1:
				break;

			default:
				abort ();
		}
	} while (nextOption != -1);
	workingDirectory = argv[optind];
	check(databaseHost != NULL && databaseUser != NULL && databasePassword != NULL && databaseSchema != NULL, "Usage:\n\t%s -H <database_host> -s <database_schema> -u <database_user> -p <database_password> <working_directory>", programName);
	check(chdir(workingDirectory) == 0, "Cannot set working directory.");
	check(mysql_library_init(0, NULL, NULL) == 0, "MySQL library initialization has failed.");
	mysqlHandle = InitMysqlConnection(databaseHost, databaseUser, databasePassword, databaseSchema);
	check(mysqlHandle != NULL, "Connection to MySQL database has failed.");
	check(CleanDatabase(mysqlHandle, SQL_QUERY_LISTCATS, CleanCategories) == true, "Cleaning categories has failed.");
	check(CleanDatabase(mysqlHandle, SQL_QUERY_LISTCLS, CleanLinksAttitude) == true, "Cleaning categories has failed.");
	check(CleanDatabase(mysqlHandle, SQL_QUERY_LISTLINKS, CleanLinks) == true, "Cleaning categories has failed.");
	check(CleanDatabase(mysqlHandle, SQL_QUERY_LISTCF, CleanLinksCf) == true, "Cleaning categories has failed.");
	check(CleanDatabase(mysqlHandle, SQL_QUERY_LISTCFATT, CleanLinksCfAtt) == true, "Cleaning categories has failed.");
	check(CleanDatabase(mysqlHandle, SQL_QUERY_LISTACL, CleanCategoriesAcl) == true, "Cleaning categories ACL has failed.");
	filesCount = scandir(workingDirectory, &linkFiles, FilterLinkFiles, alphasort);
	check(linkFiles != NULL && filesCount > 0, "No files were found.");
	for (int i = 0; i < filesCount; i++)
	{
		check(CleanFileSystem(mysqlHandle, linkFiles[i]->d_name) == true, "Cleaning file system has failed.");
	}
	result = 0;
error:
	if (linkFiles != NULL)
	{
		for (int i = 0; i < filesCount; i++)
			if (linkFiles[i] != NULL)
				free(linkFiles[i]);
		free(linkFiles);
	}
	CloseMysqlConnection(mysqlHandle);		
	mysql_library_end();
	return result;
}
