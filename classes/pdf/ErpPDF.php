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

class ErpPDF extends PDFCore
{

	// Surcharge adding the orientation
	public function render($display = true, $orientation = 'P')
	{
			$render = false;
			$this->pdf_renderer->setFontForLang($this->context->language->iso_code);
			foreach ($this->objects as $object)
			{
					$template = $this->getTemplateObject($object);
					if (!$template)
							continue;

					if (empty($this->filename))
					{
							$this->filename = $template->getFilename();
							if (count($this->objects) > 1)
									$this->filename = $template->getBulkFilename();
					}

					$template->assignHookData($object);

					$this->pdf_renderer->createHeader($template->getHeader());
					$this->pdf_renderer->createFooter($template->getFooter());
					$this->pdf_renderer->createContent($template->getContent());
					$this->pdf_renderer->writePage($orientation);
					$render = true;

					unset($template);
			}

			if ($render)
					return $this->pdf_renderer->render($this->filename, $display);
	}
}