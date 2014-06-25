<?php

/**
 * Copyright (C) 2014 HB Agency
 * 
 * @author		Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @link		http://www.hbagency.com
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
