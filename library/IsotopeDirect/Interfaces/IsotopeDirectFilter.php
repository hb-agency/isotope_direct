<?php

/**
 * Copyright (C) 2015 Rhyme Digital, LLC
 *
 * @author		Blair Winans <blair@rhyme.digital>
 * @author		Adam Fisher <adam@rhyme.digital>
 * @link		http://rhyme.digital
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace IsotopeDirect\Interfaces;

use Isotope\Model\Product;
use Isotope\Model\ProductCategory;

/**
 * Interface IsotopeDirectFilter
 * Base class for IsotopeDirect filters
 */
interface IsotopeDirectFilter
{

    /**
     * Add this filter to the module's template or get the URL params
     * @param   array
     * @param   Contao\Template
     * @param   Contao\Module
     * @param   boolean
     * @return  mixed string|bool|void
     */
    public static function generateFilter(&$arrCategories, &$objTemplate, $objModule, $blnGenURL=false);

}
