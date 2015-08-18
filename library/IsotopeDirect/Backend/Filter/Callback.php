<?php

/**
 * Copyright (C) 2015 Rhyme Digital, LLC
 *
 * @author		Blair Winans <blair@rhyme.digital>
 * @author		Adam Fisher <adam@rhyme.digital>
 * @link		http://rhyme.digital
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace IsotopeDirect\Backend\Filter;


class Callback extends \Backend
{

    /**
     * Get the filter types and return them as array
     * @return array
     */
    public function getFilterTypes()
    {
        $arrTypes = array();

        foreach ($GLOBALS['PRODUCT_FILTERS'] as $type => $arrData)
        {
			$arrTypes[$type] = strlen($arrData['label']) ? $arrData['label'] : $type;
        }

        return $arrTypes;
    }
}
