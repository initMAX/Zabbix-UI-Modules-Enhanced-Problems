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

namespace Modules\EnhancedProblems;

use CController as CAction;
use Core\CModule as ModuleBase;
use Modules\EnhancedProblems\Actions\CreateIssue;

require_once 'CWidgetConfig.php';

class Module extends ModuleBase
{
    /**
     * Initialize module.
     */
	public function init(): void
	{
          $this->setCompatibilityMode(ZABBIX_VERSION);
	}

	/**
     * Event handler, triggered before executing the action.
     *
     * @param CAction $action Action instance responsible for current request.
     */
	public function onBeforeAction(CAction $action): void
	{
          if ($action->getAction() === 'dashboard.view')
          {
               zbx_add_post_js(file_get_contents(__DIR__.'/public/js/class.widget.enhancedproblems.js'));
          }

          if ($action instanceof CreateIssue)
          {
               $action->module = $this;
          }
	}

     protected function setCompatibilityMode($version)
     {
		if(version_compare($version, '6.0.1', '<'))
          {
               if(!function_exists('hasErrorMessages'))
               {
                    // Define function in global scope
                    eval('function hasErrorMessages() { return hasErrorMesssages(); }');
               }
		}
	}
}
