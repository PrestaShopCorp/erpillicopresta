<?php
/**
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Illicopresta SA <contact@illicopresta.com>
*  @copyright 2007-2015 Illicopresta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ErpPDFGenerator extends PDFGeneratorCore
{

	/// Surcharge adding the orientation
	public function writePage($orientation = 'P')
	{
			$this->SetHeaderMargin(5);
			$this->SetFooterMargin(18);
			$this->setMargins(10, 40, 10);
			$this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

			$this->AddPage($orientation);

			$this->writeHTML($this->content, true, false, true, false, '');
	}
}