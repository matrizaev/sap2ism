--���������� ������ �����, ���� ���������� ���������� ����������
INSERT INTO `jos_sap_filelist` VALUES (7000000000000000000000,"doc", "Ouch", 70000000000000000000) ON DUPLICATE KEY UPDATE `extension`="doc", `description`="Ouch"
SET NAMES utf8 --��������� ��������� ��������� ������
SELECT `id`, `name` FROM `jos_sap_organizations` WHERE 1 --��������� ������ �����������
SELECT `id`, `name` FROM `jos_sap_documents_types` WHERE 1 --��������� ������ ����� ����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `type` = 1 --������� ���������� ���������� ������� ����
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `type` = 3 --������� ���������� ���������� ������� ����
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7000%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7100%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7200%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7300%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7400%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7500%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7600%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT COUNT( `id` ) AS Count FROM `jos_sap_documents` WHERE `id` LIKE "7700%" AND `type` = 1 --������� ���������� ���������� � ������ �����������
SELECT * FROM `jos_sap_documents` WHERE `id` LIKE "7000%" AND `type` = 1 --��������� ���� ���������� ������� ���� � ������ �����������
--��������� ���� ����������, ���������� ������ ��������
SELECT `val`.`validity_id`, `val_types`.`name`, `docs`.`number`, `docs`.`type` FROM `jos_sap_validity` AS `val` INNER JOIN `jos_sap_validity_types` AS `val_types` ON `val`.`validity_type` = `val_types`.`id` INNER JOIN `jos_sap_documents` AS `docs` ON `val`.`validity_id` = `docs`.`id` WHERE `val`.`document_id` = "70000000000000000000"
SELECT * FROM `jos_sap_filelist` WHERE `id` LIKE "70000000000000000000%" --��������� ���� ������ ������� ��������� � ������� ���������
--��������� ���� ����������, ���������� ������ ��������
SELECT `val`.`validity_id`, `val_types`.`name`, `docs`.`number`, `docs`.`type` FROM `jos_sap_validity` AS `val` INNER JOIN `jos_sap_validity_types` AS `val_types` ON `val`.`validity_type` = `val_types`.`id` INNER JOIN `jos_sap_documents` AS `docs` ON `val`.`validity_id` = `docs`.`id` WHERE `val`.`document_id` = "70000000000000000001"
SELECT * FROM `jos_sap_filelist` WHERE `id` LIKE "70000000000000000001%" --��������� ���� ������ ������� ��������� � ������� ���������
--����� ���������� �� ��������� �����
SELECT * FROM `jos_sap_documents` WHERE `number` LIKE "%���������%" OR `description` LIKE "%���������%" OR `author` LIKE "%���������%"