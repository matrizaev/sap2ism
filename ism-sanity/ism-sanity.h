#ifndef CONFIG_H
#define CONFIG_H

#include <mysql.h>
#include <getopt.h>

//#define DATABASE_HOST			"192.168.97.85"
//#define DATABASE_USER			"root"
//#define DATABASE_PASSWORD		"5Gjy$BUe"
//#define DATABASE_SCHEMA		"joomla"
#define SQL_QUERY_LISTCATS		"SELECT `cat_id` FROM `jos_mt_cats` WHERE 1"
#define SQL_QUERY_LISTCLS		"SELECT `cl`.`cl_id` FROM `jos_mt_cl` AS `cl` LEFT JOIN `jos_mt_cats` AS `cats` ON `cl`.`cat_id` = `cats`.`cat_id` WHERE `cats`.`cat_id` IS NULL"
#define SQL_QUERY_LISTLINKS		"SELECT `link`.`link_id` FROM `jos_mt_links` AS `link` LEFT JOIN `jos_mt_cl` AS `cl` ON `link`.`link_id` = `cl`.`link_id` WHERE `cl`.`link_id` IS NULL"
#define SQL_QUERY_LISTCFATT		"SELECT `cf`.`link_id` FROM `jos_mt_cfvalues_att` AS `cf` LEFT JOIN `jos_mt_links` AS `links` on `cf`.`link_id` = `links`.`link_id` WHERE `links`.`link_id` IS NULL"
#define SQL_QUERY_LISTCF		"SELECT `cf`.`link_id` FROM `jos_mt_cfvalues` AS `cf` LEFT JOIN `jos_mt_links` AS `links` on `cf`.`link_id` = `links`.`link_id` WHERE `links`.`link_id` IS NULL"
#define SQL_QUERY_LISTACL		"SELECT `id` FROM `jos_jst_acl` WHERE `id` NOT IN (SELECT `cat_id` FROM `jos_mt_cats`)"
#define SQL_QUERY_CHECKFILENAME	"SELECT `link_id` FROM `jos_mt_cfvalues_att` WHERE `filedata` = \"/components/com_mtree/attachment.php?link_id=%u&cf_id=24\" LIMIT 1"
#define SQL_QUERY_GETPARENT		"SELECT `cat_parent` FROM `jos_mt_cats` WHERE `cat_id` = %s LIMIT 1"
#define SQL_QUERY_DELETECAT		"DELETE FROM `jos_mt_cats` WHERE `cat_id` = %s LIMIT 1"
#define SQL_QUERY_DELETELINK	"DELETE FROM `jos_mt_links` WHERE `link_id` = %s LIMIT 1"
#define SQL_QUERY_DELETECL		"DELETE FROM `jos_mt_cl` WHERE `cl_id` = %s LIMIT 1"
#define SQL_QUERY_DELETECFATT	"DELETE FROM `jos_mt_cfvalues_att` WHERE `link_id` = %s LIMIT 1"
#define SQL_QUERY_DELETECF		"DELETE FROM `jos_mt_cfvalues` WHERE `link_id` = %s LIMIT 1"
#define SQL_QUERY_DELETEACL		"DELETE FROM `jos_jst_acl` WHERE `id` = %s"


#define ERROR_STR_INVALIDINPUT	"Function input is invalid."

#endif //CONFIG_H
