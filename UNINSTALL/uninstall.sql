#############################################################################################
# Druckbare Preisliste 2.0.3 Uninstall - 2019-03-13- Klartexter
# NUR AUSFÜHREN WENN SIE DAS MODUL VOLLSTÄNDIG AUS DER DATENBANK ENTFERNEN WOLLEN!!!!!
#############################################################################################

DELETE FROM configuration WHERE configuration_key LIKE 'PL_%';
DELETE FROM configuration_group WHERE configuration_group_title='Printable Price-list';
DELETE FROM configuration_group WHERE configuration_group_title LIKE 'Price-list Profile-%';
DELETE FROM admin_pages WHERE page_key LIKE 'config%Pricelist%';
DELETE FROM configuration_language WHERE configuration_key LIKE 'PL_%';