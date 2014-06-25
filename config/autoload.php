<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Isotope_direct
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('IsotopeDirect', 'system/modules/isotope_direct/library');


/**
 * Register classes outside the namespace folder
 */
/*NamespaceClassLoader::addClassMap(array
(
    // Drivers
    'DC_ProductData'                    => 'system/modules/isotope/drivers/DC_ProductData.php',
    'DC_TablePageId'                    => 'system/modules/isotope/drivers/DC_TablePageId.php',
));*/


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'iso_filter_direct_default' 	 => 'system/modules/isotope_direct/templates/isotope',
	'mod_productlist_direct'         => 'system/modules/isotope_direct/templates/modules',
));
