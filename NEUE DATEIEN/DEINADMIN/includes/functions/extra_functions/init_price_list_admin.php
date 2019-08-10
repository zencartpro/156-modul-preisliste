<?php
// -----
// SQL installation script for the Printable Pricelist Zen Cart plugin.
//
// Based on the configuration settings provided in the pricelist-3.sql file provided in v1.5.0 of this plugin.
//
define ('PL_CURRENT_VERSION', 'v2.0.4');

// -----
// First, install the main options.
//
$config_group_title = 'Printable Price-list';
$config_info = $db->Execute ("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title='$config_group_title' LIMIT 1");
if ($config_info->EOF) {
  $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " 
                 (configuration_group_title, configuration_group_description, sort_order, visible) 
                 VALUES ('$config_group_title', 'The main options for the printable price-list module are stored here.', '1', '1');");
  $cgi = $db->Insert_ID(); 
  $db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $cgi WHERE configuration_group_id = $cgi;");
  
} else {
  $cgi = $config_info->fields['configuration_group_id'];
  
}
if (!defined ('PL_INSTALLED_VERSION')) {
  $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Installed Version', 'PL_INSTALLED_VERSION', '" . PL_CURRENT_VERSION . "', 'The plugin version currently installed.', $cgi , 10, NULL , 'trim(' )");
  
  $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Default Profile', 'PL_DEFAULT_PROFILE', '1', 'Choose the default profile to use.', $cgi , 10, NULL , 'zen_cfg_select_option(array(\'1\', \'2\', \'3\' ),' )");
  
  $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Profile Links?', 'PL_SHOW_PROFILES', 'true', 'Choose <em>true</em> to display links to the currently-enabled profiles on the <em>pricelist</em> page.', $cgi, 20, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");
  
  $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Information Sidebox Link?', 'PL_SHOW_INFO_LINK', 'true', 'Choose whether (<em>true</em>) or not (<em>false</em>) a &quot;Price List&quot; link should be shown in the Information sidebox.', $cgi, 30, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");
  
  define ('PL_INSTALLED_VERSION', PL_CURRENT_VERSION);
  
}
if (PL_INSTALLED_VERSION != PL_CURRENT_VERSION) {
  $db->Execute ("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . PL_CURRENT_VERSION . "' WHERE configuration_key='PL_INSTALLED_VERSION' LIMIT 1");
  
}
if (!zen_page_key_exists ('configPrintablePricelist')) {
  zen_register_admin_page ('configPrintablePricelist', 'BOX_CONFIGURATION_PL', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y', $cgi);
  
}

if (!defined ('PL_INCLUDE_CURRENCY_SYMBOL')) {
  $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Include currency symbol in pricelist header?', 'PL_INCLUDE_CURRENCY_SYMBOL', 'true', 'Choose whether (<em>true</em>) or not (<em>false</em>) the currently-selected currencies\' symbol should be included in the pricelist print-out.', $cgi, 40, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");
  
}

// -----
// Next, install the three (3) profiles' configurations.
//
for ($profile = 1; $profile <= 3; $profile++) {
  $config_group_title = "Price-list Profile-$profile";
  $config_info = $db->Execute ("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title='$config_group_title' LIMIT 1");
  if ($config_info->EOF) {
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " 
                   (configuration_group_title, configuration_group_description, sort_order, visible) 
                   VALUES ('$config_group_title', 'Settings for printable price-list profile-$profile.', '1', '1')");
    $cgi = $db->Insert_ID(); 
    $db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $cgi WHERE configuration_group_id = $cgi;");
    
  } else {
    $cgi = $config_info->fields['configuration_group_id'];
    
  }
  if (!defined ("PL_ENABLE_$profile")) {
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Enable Profile?', 'PL_ENABLE_$profile', 'true', 'Choose <em>true</em> to enable this price-list profile to be used on the <em>pricelist</em> page.', $cgi, 10, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Group Name', 'PL_GROUP_NAME_$profile', '', 'Set this field to a <b>Group Name</b> (see <em>Customers->Group Pricing</em>) to enable this profile <em>only</em> for customers in that group. Leave the field empty for the profile to apply to all customers.', $cgi, 15, NULL , NULL )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Profile Name', 'PL_PROFILE_NAME_$profile', 'Product Profile $profile', 'Give this profile a name.', $cgi, 20, NULL , NULL )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Display Linked Products?', 'PL_USE_MASTER_CATS_ONLY_$profile', 'false', 'Should products be listed under all linked categories (<em>false</em>) or only under their master-category (<em>true</em>)?', $cgi, 32, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Selections?', 'PL_SHOW_BOXES_$profile', 'true', 'Set this value to <em>true</em> to display language and currency selections as well as a categories dropdown menu.', $cgi, 35, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Categories Dropdown: Main Only?', 'PL_CATEGORY_TREE_MAIN_CATS_ONLY_$profile', 'true', 'Should the categories dropdown menu contain <em>only</em> the mail categories?  If set to <em>false</em>, then <b>all</b> categories are displayed.  <b>Note:</b> This setting is ignored if <em>Show Selections</em> is set to <em>false</em>', $cgi, 37, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");
    
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Main Categories: New Page', 'PL_MAINCATS_NEW_PAGE_$profile', 'false', 'If true, main categories on the printed price-list will start on a new page.', $cgi, 40, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'No Wrap', 'PL_NOWRAP_$profile', 'false', 'To enable or disable wrapping on screen (nowrap is easier for debugging)', $cgi, 60, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Model', 'PL_SHOW_MODEL_$profile', 'true', 'Display each product\'s model number in a separate column?', $cgi, 100, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Manufacturer', 'PL_SHOW_MANUFACTURER_$profile', 'true', 'Display each product\'s manufacturer in a separate column?', $cgi, 105, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Weight', 'PL_SHOW_WEIGHT_$profile', 'false', 'Display each product\'s weight in a separate column?', $cgi, 110, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Stock-on-Hand', 'PL_SHOW_SOH_$profile', 'false', 'Display each product\'s stock-on-hand in a separate column?', $cgi, 115, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Notes (A)', 'PL_SHOW_NOTES_A_$profile', 'false', 'Display an empty column for each product, allowing the customer to make notes?', $cgi, 120, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Notes (B)', 'PL_SHOW_NOTES_B_$profile', 'false', 'Display another empty column for each product, allowing the customer to make notes?', $cgi, 125, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns:  Price', 'PL_SHOW_PRICE_$profile', 'true', 'Display each product\'s price, including or excluding tax based on your shop\'s tax-configuration settings)?', $cgi, 130, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Price (ex)', 'PL_SHOW_TAX_FREE_$profile', 'false', 'Display each product\'s tax-free price in a separate column?', $cgi, 135, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Specials Prices?', 'PL_SHOW_SPECIAL_PRICE_$profile', 'true', 'Display each product\'s &quot;special&quot; price?  If <em>true</em>, the script will execute 4 extra queries per product!', $cgi, 140, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Specials Expiry?', 'PL_SHOW_SPECIAL_DATE_$profile', 'false', 'Show special price expiry date?  This works <em>only</em> for specials (not for pricing by attributes and sales). Executes one extra query per special if enabled.', $cgi, 145, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");
    
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Columns: Add-to-Cart', 'PL_SHOW_ADDTOCART_BUTTON_$profile', 'false', 'Display an add-to-cart button for each product? If the product has attributes, a &quot;More info&quot; link displays instead.', $cgi, 150, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");
    
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Add-to-Cart Button Target', 'PL_ADDTOCART_TARGET_$profile', 'Cartpage', 'How to react to an Add-to-Cart button click: <em>Cartpage</em> sends all results to the same web page, <em>_self</em> sends result to the current page and <em>_blank</em> sends each result to a new page.', $cgi, 155, NULL , 'zen_cfg_select_option(array(\'Cartpage\', \'_self\', \'_blank\'),' )");
    
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Product Images?', 'PL_SHOW_IMAGE_$profile', 'false', 'Display each product\'s image?', $cgi, 160, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Image Height', 'PL_IMAGE_PRODUCT_HEIGHT_$profile', '80', 'If the product images are to be displayed, what is the height of each image?', $cgi, 165, NULL , NULL )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Image Width', 'PL_IMAGE_PRODUCT_WIDTH_$profile', '100', 'If the product images are to be displayed, what is the width of each image?', $cgi, 170, NULL , NULL )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Descriptions?', 'PL_SHOW_DESCRIPTION_$profile', 'false', 'Diaplay each product\'s description?', $cgi, 175, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Truncate Descriptions?', 'PL_TRUNCATE_DESCRIPTION_$profile', '300', 'If <em>Show Descriptions?</em> is set to <b>true</b> and this field is a value other than 0 or blank, product descriptions will be truncated to this length &mdash; HTML will be stripped.', $cgi, 180, NULL , NULL )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Inactive Products and Categories?', 'PL_SHOW_INACTIVE_$profile', 'false', 'Set this value to <em>true</em> to include disabled products and categories in the list.', $cgi, 200, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Sort By: Field', 'PL_SORT_PRODUCTS_BY_$profile', 'products_price', 'How products are sorted within a category', $cgi, 210, NULL , 'zen_cfg_select_option(array(\'products_name\', \'products_price\', \'products_model\' ),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Sort By: Asc/Desc', 'PL_SORT_ASC_DESC_$profile', 'asc', 'Sort ascending or descending', $cgi, 215, NULL , 'zen_cfg_select_option(array(\'asc\', \'desc\' ),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Enable Debug?', 'PL_DEBUG_$profile', 'false', 'If true debug info is shown', $cgi, 200, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Store Logo On-Screen?', 'PL_HEADER_LOGO_$profile', 'true', 'Display the store\'s logo at the top of the screen?', $cgi, 260, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Page Headers?', 'PL_SHOW_PRICELIST_PAGE_HEADERS_$profile', 'false', 'If true the page headers on each page are shown (screen and print).', $cgi, 270, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function ) VALUES ( 'Show Page Footers?', 'PL_SHOW_PRICELIST_PAGE_FOOTERS_$profile', 'true', 'If true the page footers on each page are shown (screen and print).', $cgi, 280, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' )");
    
  }
  if (!zen_page_key_exists ("configPricelistProfile$profile")) {
    zen_register_admin_page ("configPricelistProfile$profile", "BOX_CONFIGURATION_PL_$profile", 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y', $cgi);
    
  }
  
$db->Execute("REPLACE INTO ".TABLE_CONFIGURATION_LANGUAGE." (configuration_title, configuration_key, configuration_description, configuration_language_id) VALUES
('Installierte Version', 'PL_INSTALLED_VERSION', 'Die aktuell installierte Plugin-Version', 43),
('Standardprofil', 'PL_DEFAULT_PROFILE', 'Wählen Sie das zu verwendende Standardprofil aus', 43),
('Profil-Links anzeigen?', 'PL_SHOW_PROFILES', 'Wählen Sie <em>true</em>, um Links zu den aktuell aktivierten Profilen auf der Seite <em>Preisliste</em> anzuzeigen.', 43),
('Sidebox Link bei der Information anzeigen?', 'PL_SHOW_INFO_LINK', 'Wählen Sie, ob (<em>true</em>) oder nicht (<em>false</em>) ein &quot; Preisliste&quot; Link in der Information Sidebox angezeigt werden soll.', 43),
('Gruppenname', 'PL_GROUP_NAME_$profile', 'Setzt dieses Feld auf einen <b>Gruppennamen</b> (siehe <em>Kunden->Gruppenpreisgestaltung</em>), um dieses Profil <em>nur</em> für Kunden in dieser Gruppe zu aktivieren. Lassen Sie das Feld leer, damit das Profil für alle Kunden gilt.', 43),
('Währungssymbol in den Kopf der Preisliste aufnehmen?', 'PL_INCLUDE_CURRENCY_SYMBOL', '<br />Wählen Sie, ob (<em>true</em>) oder nicht (<em>false</em>) das Symbol der aktuell ausgewählten Währungen im Ausdruck der Preisliste enthalten sein soll.', 43),
('Profil aktivieren?', 'PL_ENABLE_$profile', 'Wählen Sie <em>true</em>, um dieses Preislistenprofil auf der Seite <em>Preisliste</em> zu verwenden.', 43),
('Profilname', 'PL_PROFILE_NAME_$profile', 'Geben Sie diesem Profil einen Namen.', 43),
('Verknüpfte Produkte anzeigen?', 'PL_USE_MASTER_CATS_ONLY_$profile', 'Sollen Produkte unter allen verknüpften Kategorien (<em>false</em>) oder nur unter ihrer Master-Kategorie (<em>true</em>) aufgeführt werden?', 43),
('Auswahl anzeigen?', 'PL_SHOW_BOXES_$profile', 'Stellen Sie diesen Wert auf <em>true</em>, um Sprach- und Währungsauswahlen sowie ein Dropdown-Menü für Kategorien anzuzeigen.', 43),
('Dropdown-Liste Kategorien: Nur Hauptseite?', 'PL_CATEGORY_TREE_MAIN_CATS_ONLY_$profile', 'Soll das Dropdown-Menü für Kategorien <em>nur</em> die Mailkategorien enthalten?  Wenn auf <em>false</em> gesetzt, dann werden <b>alle</b> Kategorien angezeigt.  <b>Hinweis:</b> Diese Einstellung wird ignoriert, wenn <em>Auswahl anzeigen</em> auf <em>false</em> gesetzt ist.', 43),
('Hauptkategorien: Neue Seite', 'UPL_MAINCATS_NEW_PAGE_$profile', 'Wenn das zutrifft, beginnen die Hauptkategorien auf der gedruckten Preisliste auf einer neuen Seite.', 43),
('Kein Zeilenumbruch', 'PL_NOWRAP_$profile', 'So aktivieren oder deaktivieren Sie das Zeilenumbruchverfahren auf dem Bildschirm (nowrap ist einfacher für das Debugging)', 43),
('Spalten: Modell', 'PL_SHOW_MODEL_$profile', 'Die Modellnummer jedes Produkts in einer separaten Spalte anzeigen?', 43),
('Spalten: Hersteller', 'PL_SHOW_MANUFACTURER_$profile', 'Den Hersteller jedes Produkts in einer separaten Spalte anzeigen?', 43),
('Spalten: Gewicht', 'PL_SHOW_WEIGHT_$profile', 'Das Gewicht jedes Produkts in einer separaten Spalte anzeigen?', 43),
('Spalten: Vorratsbestand', 'PL_SHOW_SOH_$profile', 'Die Lagerbestände der einzelnen Produkte in einer separaten Spalte anzeigen?', 43),
('Spalten: Anmerkungen (A)', 'L_SHOW_NOTES_A_$profile', 'Eine leere Spalte für jedes Produkt anzeigen, damit der Kunde Notizen machen kann?', 43),
('Spalten: Anmerkungen (B)', 'PL_SHOW_NOTES_B_$profile', 'Eine weitere leere Spalte für jedes Produkt anzeigen, damit der Kunde Notizen machen kann?', 43),
('Spalten:  Preis', 'PL_SHOW_PRICE_$profile', 'Den Preis jedes Produkts anzeigen, einschließlich oder ausschließlich Steuern, basierend auf den Steuerkonfigurationseinstellungen Ihres Shops?', 43),
('Spalten: Preis (ex)', 'PL_SHOW_TAX_FREE_$profile', 'Den steuerfreien Preis jedes Produkts in einer separaten Spalte anzeigen?', 43),
('Sonderpreise anzeigen?', 'PL_SHOW_SPECIAL_PRICE_$profile', 'Jedes Einzelprodukt mit dem Sonderpreis anzeigen?  Wenn <em>true</em>, wird das Skript 4 zusätzliche Abfragen pro Produkt ausführen!', 43),
('Sonderangebote mit Ablaufdatum anzeigen?', 'PL_SHOW_SPECIAL_DATE_$profile', 'Sonderpreisverfallsdatum anzeigen?  Dies funktioniert <em>nur</em> für Sonderangebote (nicht für die Preisgestaltung nach Attributen und Verkäufen). Führt eine zusätzliche Abfrage pro Special aus, wenn aktiviert.', 43),
('Spalten: Zum Warenkorb hinzufügen', 'PL_SHOW_ADDTOCART_BUTTON_$profile', 'Eine Schaltfläche zum Hinzufügen des Warenkorbs für jedes Produkt anzeigen? Wenn das Produkt Attribute hat, wird stattdessen ein &quot; Mehr Infos&quot; Link angezeigt.', 43),
('Warenkorb-Taste Zielvorgabe', 'PL_ADDTOCART_TARGET_$profile', 'Wie kann man auf einen Klick auf die Schaltfläche In den Warenkorb reagieren? <em>Cartpage</em> sendet alle Ergebnisse an dieselbe Webseite, <em>_self</em> sendet Ergebnisse an die aktuelle Seite und <em>_blank</em> sendet jedes Ergebnis an eine neue Seite.', 43),
('Produktbilder anzeigen?', 'PL_SHOW_IMAGE_$profile', 'Das Bild jedes Produktes anzeigen?', 43),
('Bildhöhe', 'PL_IMAGE_PRODUCT_HEIGHT_$profile', 'Wenn die Produktbilder angezeigt werden sollen, wie groß ist die Höhe der einzelnen Bilder?', 43),
('Bildbreite', 'PL_IMAGE_PRODUCT_WIDTH_$profile', 'Wenn die Produktbilder angezeigt werden sollen, wie breit ist dann jedes Bild?', 43),
('Beschreibungen anzeigen?', 'PL_SHOW_DESCRIPTION_$profile', 'Die Beschreibung der einzelnen Produkte in einer Liste anzeigen?', 43),
('Beschreibungen kürzen?', 'PL_TRUNCATE_DESCRIPTION_$profile', 'Wenn <em>Beschreibungen zeigen?</em> auf <b>true</b> gesetzt ist und dieses Feld ein anderer Wert als 0 oder leer ist, werden Produktbeschreibungen auf diese Länge reduziert &mdash; HTML wird entfernt.', 43),
('Inaktive Produkte und Kategorien anzeigen?', 'PL_SHOW_INACTIVE_$profile', 'Setzen Sie diesen Wert auf <em>true</em>, um deaktivierte Produkte und Kategorien in die Liste aufzunehmen.', 43),
('Sortieren nach: Feld', 'PL_SORT_PRODUCTS_BY_$profile', 'Wie Produkte innerhalb einer Kategorie sortiert werden', 43),
('Sortieren nach Asc/Desc', 'PL_SORT_ASC_DESC_$profile', 'Sortieren aufsteigend oder absteigend', 43),
('Debuggen aktivieren?', 'PL_DEBUG_$profile', 'Wenn eine echte Debug-Info angezeigt wird', 43),
('Shop-Logo auf dem Bildschirm anzeigen?', 'PL_HEADER_LOGO_$profile', 'Das Logo des Shops oben auf dem Bildschirm anzeigen?', 43),
('Seitenüberschriften anzeigen?', 'PL_SHOW_PRICELIST_PAGE_HEADERS_$profile', 'Wenn true, werden die Seitenköpfe auf jeder Seite angezeigt (Bildschirm und Druck).', 43),
('Seitenfußzeilen anzeigen?', 'PL_SHOW_PRICELIST_PAGE_FOOTERS_$profile','Wenn true, werden die Fußzeilen auf jeder Seite angezeigt (Bildschirm und Druck).', 43)");  
  // -----
  // Rename existing configuration key for consistent naming strategy.
  //
  if (defined ("TEXT_PL_HEADER_LOGO_$profile")) {
    $db->Execute ("UPDATE " . TABLE_CONFIGURATION . " SET configuration_key = 'PL_HEADER_LOGO_$profile' WHERE configuration_key = 'TEXT_PL_HEADER_LOGO_$profile' LIMIT 1");
    
  }
  
}