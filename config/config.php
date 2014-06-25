<?php

/**
 * Copyright (C) 2014 HB Agency
 * 
 * @author		Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @link		http://www.hbagency.com
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */



/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['isotope']['iso_productlist_direct']					= 'IsotopeDirect\Module\ProductList';
$GLOBALS['FE_MOD']['isotope']['iso_productfilter_direct']				= 'IsotopeDirect\Module\ProductFilter';



/**
 * Filter types
 */
$GLOBALS['PRODUCT_FILTERS'] = array
(
    'keywords' => array
    (
        'class'		=> 'IsotopeDirect\Filter\Keywords',
        'label'		=> &$GLOBALS['TL_LANG']['MSC']['keywordsFilterLabel'],
    ),
    'sorting' => array
    (
        'class'		=> 'IsotopeDirect\Filter\Sorting',
        'label'		=> &$GLOBALS['TL_LANG']['MSC']['sortingFilterLabel'],
    ),
    'pricerange' => array
    (
        'class'		=> 'IsotopeDirect\Filter\PriceRange',
        'label'		=> &$GLOBALS['TL_LANG']['MSC']['pricerangeFilterLabel'],
    ),
);

