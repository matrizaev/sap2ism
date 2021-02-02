#ifndef CONFIG_H
#define CONFIG_H

#include <mysql.h>
#include <libxml/xmlmemory.h>
#include <libxml/parser.h>
#include <libxml/xmlschemas.h>
#include <libxml/tree.h>

#define SCHEMA_FILENAME "scheme.xsd"
#define DATABASE_HOST "91.208.210.38"
#define DATABASE_USER "root"
#define DATABASE_PASSWORD "5Gjy$BUe"
#define DATABASE_SCHEMA "sap2ism"
#define ERROR_DIRECTORY "./errors/"
#define ERROR_STR_INVALIDINPUT	"Function input is invalid."

#define SQL_QUERY_INSERTDOCUMENT	"INSERT INTO `sap_documents`(`doc_id`, `doc_type`, `doc_number`, `doc_author`, `doc_date`, `doc_description`, `doc_dep`) "\
									"VALUES(\"%s\", %s, \"%s\", \"%s\", \"%s\", \"%s\", \"%s\") "\
									"ON DUPLICATE KEY UPDATE `doc_type`=VALUES(`doc_type`), "\
									"`doc_number`=VALUES(`doc_number`), `doc_author`=VALUES(`doc_author`), `doc_date`=VALUES(`doc_date`), "\
									"`doc_description`=VALUES(`doc_description`), `doc_dep`=VALUES(`doc_dep`)"
#define SQL_QUERY_INSERTVALIDITY	"INSERT INTO `sap_validity`(`doc_id`, `val_id`, `val_type`) VALUES(\"%s\",\"%s\",%d) "\
									"ON DUPLICATE KEY UPDATE `doc_id`=VALUES(`doc_id`)"
#define SQL_QUERY_INSERTFILELIST	"INSERT INTO `sap_filelist`(`doc_id`, `file_id`, `file_extension`, `file_description`) VALUES(\"%s\",\"%.*s\",\"%s\",\"%s\") "\
									"ON DUPLICATE KEY UPDATE `file_extension`=VALUES(`file_extension`), `file_description`=VALUES(`file_description`)"
#define SQL_QUERY_DELETEDOCUMENT	"DELETE FROM `sap_documents` WHERE `doc_id` = \"%s\""

//val_id - кто меняет


struct TQueryListStruct
{
	char *query;
	struct TQueryListStruct *next;
};
typedef struct TQueryListStruct TQueryList;

extern TQueryList *ParseXmlFile(MYSQL *mysqlHandle, const char *xmlFile);

extern bool QueryListAdd(TQueryList **queries, char *str);

extern bool QueryListAppend(TQueryList *queries, TQueryList *appends);

extern void QueryListFree(TQueryList *queries);

#endif // CONFIG_H
