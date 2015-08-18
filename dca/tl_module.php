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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist_direct'] = str_replace('{config_legend},', '{config_legend},iso_searchFields,', $GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist']);
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productfilter_direct'] = str_replace(array('{config_legend},','iso_searchFields,'), array('{config_legend},iso_filterTypes,',''), $GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productfilter']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterTypes'] = array
(
    'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_filterTypes'],
    'exclude'					=> true,
    'inputType'					=> 'checkboxWizard',
    'options_callback'			=> array('IsotopeDirect\Backend\Filter\Callback', 'getFilterTypes'),
    'eval'						=> array('multiple'=>true, 'tl_class'=>'w50 w50h clr'),
    'sql'						=> "blob NULL",
);