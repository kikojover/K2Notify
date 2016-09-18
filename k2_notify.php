<?php
/**
 * @version		2.2
 * @package		Example K2 Plugin (K2 plugin)
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

/**
 * Example K2 Plugin to render YouTube URLs entered in backend K2 forms to video players in the frontend.
 */

// Load the K2 Plugin API
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR.'/components/com_k2/lib/k2plugin.php');
JFactory::getLanguage()->load('plg_k2_k2_notify',JPATH_ADMINISTRATOR);

// Initiate class to hold plugin events
class plgK2K2_notify extends K2Plugin
{

	// Some params
	var $pluginName = 'k2_notify';
	var $pluginNameHumanReadable = 'K2 Notify';

	function plgK2K2_notify(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}

	/**
	 * Below we list all available FRONTEND events, to trigger K2 plugins.
	 * Watch the different prefix "onK2" instead of just "on" as used in Joomla! already.
	 * Most functions are empty to showcase what is available to trigger and output. A few are used to actually output some code for example reasons.
	 */
      
	function onAfterK2Save(&$row, $params)
	{
            $mainframe = JFactory::getApplication();
            //Send any notification?
            $plg_parms = $this->params;
            $from = $plg_parms->get('from_notify');
            $categories = $plg_parms->get('category_id');
            $notify_on_edit = $plg_parms->get('notify_on_edit');
            $mailto = $plg_parms->get('email_to');
            $can_send = false;
            if($params || (!$params && $notify_on_edit == 1)){
                if($mainframe->isSite() && ($from == 1 || $from == 3)){
                    $can_send = true;
                }
                if($mainframe->isAdmin() && ($from == 2 || $from == 3)){
                    $can_send = true;
                }
            }
            $can_send = $can_send && in_array($row->catid, $categories) && !empty($mailto);
            
            if($can_send){
                $admin_uri = '';
                if($mainframe->isSite()) $admin_uri = 'administrator/';
                $mailer = JFactory::getMailer();
                $config = JFactory::getConfig();
                $sender = array( 
                    $config->get( 'mailfrom' ),
                    $config->get( 'fromname' ) );

                $mailer->setSender($sender);
                //Admin mail
                $mailer->addRecipient($mailto);

                $mailer->setSubject(JText::_('PLG_KNOTIFY_EMAIL_SUBJECT').' - '.$config->get( 'sitename' ));
                $body .= JText::_('PLG_KNOTIFY_EMAIL_BODY')."\n";
                $body .= $row->title."\n";
                $body .= JText::_('PLG_KNOTIFY_EMAIL_URL')."\n";
                $body .= JURI::base().$admin_uri.'index.php?option=com_k2&view=item&cid='.$row->id."\n\n";

                $mailer->isHTML(false);
                $mailer->setBody($body);
                $send =& $mailer->Send();
            }
	}
        

} // END CLASS
