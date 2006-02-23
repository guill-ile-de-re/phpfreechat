<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_send extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $msg)
  {
    $c =& $this->c;

    // check the nick is not allready known
    $nick = phpFreeChat::FilterSpecialChar($c->nick);
    $text = phpFreeChat::PreFilterMsg($msg);
        
    $errors = array();
    if ($text == "") $errors[$c->prefix."words"] = _pfc("Text cannot be empty");
    if ($nick == "") $errors[$c->prefix."handle"] = _pfc("Please enter your nickname");
    if (count($errors) == 0)
    {
      $container =& $c->getContainerInstance();
      $container->writeMsg($nick, $text);
      if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: a user just sent a message -> nick=".$c->nick." m=".$text, "chat", $c->getId());
    	
      // a message has been posted so :
      // - read new messages
      // - give focus to "words" field
      $xml_reponse->addScript("pfc.clearError(Array('".$c->prefix."words"."','".$c->prefix."handle"."'));");
      $xml_reponse->addScript("$('".$c->prefix."words').focus();");
    }
    else
    {
      // an error occured, just ignore the message and display errors
      foreach($errors as $e)
        if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: user can't send a message -> nick=".$c->nick." err=".$e, "chat", $c->getId());
      $cmd =& pfcCommand::Factory("error", $c);
      $cmd->run($xml_reponse, $clientid, $errors);
      if (isset($errors[$c->prefix."handle"])) // the nick is empty so give it focus
        $xml_reponse->addScript("$('".$c->prefix."handle').focus();");
    }
  }
}

?>