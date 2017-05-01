<?php

/**
 * Copyright (C) 2015 Rhyme Digital, LLC
 *
 * @author		Blair Winans <blair@rhyme.digital>
 * @author		Adam Fisher <adam@rhyme.digital>
 * @link		http://rhyme.digital
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
    'pricerange' => array
    (
        'class'		=> 'IsotopeDirect\Filter\PriceRange',
        'label'		=> &$GLOBALS['TL_LANG']['MSC']['pricerangeFilterLabel'],
    ),
    'perpage' => array
    (
        'class'		=> 'IsotopeDirect\Filter\PerPage',
        'label'		=> &$GLOBALS['TL_LANG']['MSC']['perpageFilterLabel'],
    ),
    'sorting' => array
    (
        'class'		=> 'IsotopeDirect\Filter\Sorting',
        'label'		=> &$GLOBALS['TL_LANG']['MSC']['sortingFilterLabel'],
    ),
);

