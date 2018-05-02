<?php
/**
 * @Copyright
 * @package     System - Google Analytics Opt Out - Plug In
 * @author      Clemens Neubauer {@link https://github.com/cn-tools/}
 * @date        Created on 26-April-2018
 * @link        Project Site {@link https://github.com/cn-tools/plg_cntools_google_analytics_optout}
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');

class PlgSystemPlg_cntools_google_analytics_optout extends JPlugin
{
	var $_AnalyticsID;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.5
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	protected $autoloadLanguage = true;

	/*-------------------------- onContentAfterSave -------------------------*/
	public function onExtensionBeforeSave($context, $table, $isNew)
	{
		$lResult = true;
		if (($table->enabled == 1) and ($table->element == 'plg_cntools_google_analytics_optout'))
		{
			$params = json_decode($table->params);
			if (!$this->checkAnalyticsID(strval($params->uaid)))
			{
				$this->app->enqueueMessage(JText::_('PLG_CNTOOLS_GOOGLE_ANALYTICS_OPTOUT_ALERT_PROPERTYID_INVALID'), 'error');
				$lResult = false;
			}
			
		}
		return $lResult;
	}

	/*-------------------------- onBeforeCompileHead -------------------------*/
	public function onBeforeCompileHead()
	{
		// Front end
		if ($this->app->isSite())
		{
			// Get plugin parameter
			$this->_AnalyticsID = htmlspecialchars($this->params->get('uaid'));

			if ($this->checkAnalyticsID($this->_AnalyticsID))
			{
				// Set tracking snipped
				JFactory::getDocument()->addScriptDeclaration($this->getAnalyticsOptOutCode($this->_AnalyticsID, htmlspecialchars($this->params->get('jsfunc'))));
			}
		}
	}

	/*-------------------------- checkAnalyticsID -------------------------*/
	private function checkAnalyticsID($aAnalyticsID)
	{
		return (preg_match('/^ua-\d{4,10}-\d{1,4}/i', $aAnalyticsID));
	}

	/*-------------------------- getAnalyticsOptOutCode -------------------------*/
	private function getAnalyticsOptOutCode($aAnalyticsID, $aJsFuncName)
	{
		$lAlertMsg = JText::_('PLG_CNTOOLS_GA_OPTOUT_ALERT');
		if ($lAlertMsg == 'PLG_CNTOOLS_GA_OPTOUT_ALERT') { $lAlertMsg =''; }
		if ($lAlertMsg != '')
		{
			$lAlertMsg = ' alert(\'' . $lAlertMsg . '\');';
		}
		
		return "var GADisableStr = 'ga-disable-" . $aAnalyticsID . "';
if (document.cookie.indexOf(GADisableStr + '=true') > -1) {
  window[GADisableStr] = true;
}
function ".$aJsFuncName."() {
  document.cookie = GADisableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
  window[GADisableStr] = true;
  ".$lAlertMsg."
}";
	}
}
?>
