<?php

/**
* @Copyright Copyright (C) 2014 - AfshinTalebi.com
**/
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.event.plugin' );

class plgContentArticlepay_assessor extends JPlugin {
	private function getArticleModel() {
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_articlepay/models');
		return JModelLegacy::getInstance( 'Article', 'ArticlepayModel' );
	}
	private function getBoughtModel() {
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_articlepay/models');
		return JModelLegacy::getInstance( 'Bought', 'ArticlepayModel' );
	}
	/**
	 * Plugin that checking user is payed cost of article 
	 *
	 * @param string $context The context of the content being passed to the plugin.
	 * @param mixed &$row An object with a "text" property or the string to be cloaked.
	 * @param mixed &$params Additional parameters. See {@see PlgContentEmailcloak()}.
	 * @param integer $page Optional page number. Unused. Defaults to zero.
	 *
	 * @return boolean True on success.
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0) {
		JPlugin::loadLanguage('plg_articlepay_assessor', JPATH_ADMINISTRATOR);
		if(!JComponentHelper::isEnabled('com_articlepay', true))
		{
			JError::raiseError ( JText::_('PLG_ARTICLEPAY_ASSESSOR_COMPONENT_ERROR_TITLE'), JText::_( 'PLG_ARTICLEPAY_ASSESSOR_COMPONENT_ERROR' ) );
			return false;
		}
		$model1=$this->getArticleModel();
		$isPayable=$model1->isArticlePayable((int)$row->id);
		if($isPayable) {
			$user = JFactory::getUser();
			$userGuestBlogPage=$user->guest && !isset($row->fulltext);
			$userGuestDetailsPage=$user->guest && isset($row->fulltext);
			$userRegisteredBlogPage=!$user->guest && !isset($row->fulltext);
			$userRegisteredBlogPage=!$user->guest && isset($row->fulltext);
			$model2=$this->getBoughtModel();
			$amount=$model1->getArticlePrice($row->id);
// 			$paymentUrl=JRoute::_("index.php?option=com_articlepay&task=pay&user=$user->id&item=$row->id&amount=$amount");
			$paymentUrl=JRoute::_("index.php?option=com_articlepay&task=pay&item=$row->id");
			if($user->guest) {
				if(isset($row->fulltext)) {
					$row->text='<div style="margin:5px;color:green;">'.
								JText::_('PLG_ARTICLEPAY_ASSESSOR_PAY_NOTE1').
								'</div>';
				} else {
					$row->text.='<div style="margin:5px;color:green;">'.
								JText::_('PLG_ARTICLEPAY_ASSESSOR_PAY_NOTE1').
								'</div>';
				}
				$row->text.='<div>'.
							'<span class="articlepay-assessor-amount">'.JText::_('PLG_ARTICLEPAY_ASSESSOR_COST_LABEL').'<span class="articlepay-assessor-amount-number" style="color:red;">'.number_format($amount).'</span>'.'</span>'.
							'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
							JHtml::link($paymentUrl,JText::_('PLG_ARTICLEPAY_ASSESSOR_PAY_ARTICLE_COST'),array('class'=>'articlepay-assessor-pay-link')).
							'</div>';
			} else {
				$isPayed=$model2->isUserPayed($user->id,(int)$row->id);
				if(!$isPayed) {
					
					if(isset($row->fulltext)) {
						$row->text='<div style="margin:5px;color:green;">'.
								JText::_('PLG_ARTICLEPAY_ASSESSOR_PAY_NOTE1').
								'</div>';
					} else {
						$row->text.='<div style="margin:5px;color:green;">'.
								JText::_('PLG_ARTICLEPAY_ASSESSOR_PAY_NOTE1').
								'</div>';
					}
					$row->text.='<div>'.
								'<span class="articlepay-assessor-amount">'.JText::_('PLG_ARTICLEPAY_ASSESSOR_COST_LABEL').'<span class="articlepay-assessor-amount-number" style="color:red;">'.number_format($amount).'</span>'.'</span>'.
								'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
								JHtml::link($paymentUrl,JText::_('PLG_ARTICLEPAY_ASSESSOR_PAY_ARTICLE_COST'),array('class'=>'articlepay-assessor-pay-link')).
								'</div>';
				}
			}
// 			set fulltext
			if(isset($row->fulltext))
				$row->fulltext=$row->text;
		}
		return true;
	}
}