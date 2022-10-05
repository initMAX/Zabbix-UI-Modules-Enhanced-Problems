<?php
/*
** initMAX
** Copyright (C) 2021-2022 initMAX s.r.o.
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 3 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

namespace Modules\EnhancedProblems\Helpers;

use CTag;
use CDiv;
use CLabel;
use CFormGrid;

class WidgetForm62
{
    protected $container;

    public function __construct(CFormGrid $container)
    {
        $this->container = $container;
    }

    public function addRow($label, $elements, $id = null, $css_class = null)
    {
        if (is_string($label) || $label === null)
        {
            $label = new CLabel($label);
        }

        if (is_array($elements) || is_a($elements, CTag::class))
        {
            if ($id !== null)
            {
                $elements->setId($id);
            }

            if ($css_class !== null)
            {
                $elements->addClass($css_class);
            }

            $elements = new CDiv($elements);
        }

        $this->container->addItem([$label, $elements]);
    }

    public function toString($destroy)
    {
        return $this->container->toString($destroy);
    }
}
