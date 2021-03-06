<?php
namespace Bitrix\Im;

class Text
{
	private static $replacements = Array();
	
	public static function parse($text, $params = Array())
	{
		if (!isset($params['SAFE']) || $params['SAFE'] == 'Y')
		{
			$text = htmlspecialcharsbx($text);
		}
		
		$allowTags = array(
			"HTML" => "N", 
			"USER" => "N", 
			"ANCHOR" => $params['LINK'] == 'N'? 'N': 'Y', 
			"BIU" => "Y", 
			"IMG" => "N", 
			"QUOTE" => "N", 
			"CODE" => "N", 
			"FONT" => "N", 
			"LIST" => "N", 
			"SMILES" => $params['SMILES'] == 'N'? 'N': 'Y', 
			"NL2BR" => "Y", 
			"VIDEO" => "N", 
			"TABLE" => "N", 
			"CUT_ANCHOR" => "N", 
			"SHORT_ANCHOR" => "N", 
			"ALIGN" => "N"
		);
		
		$parser = new \CTextParser();
		$parser->maxAnchorLength = intval($params['LINK_LIMIT'])? $params['LINK_LIMIT']: 55;
		$parser->maxStringLen = intval($params['TEXT_LIMIT']);
		$parser->allow = $allowTags;
		
		$text = preg_replace_callback("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		
		if (isset($params['CUT_STRIKE']) && $params['CUT_STRIKE'] == 'Y')
		{
			$text = preg_replace("/\[s\].*?\[\/s\]/i", "", $text);
		}
		
		$text = $parser->convertText($text);
		
		$text = str_replace(array('#BR#', '[br]', '[BR]'), '<br/>', $text);
		
		$text = self::recoverReplacements($text);
		
		return $text;
	}
	
	
	private static function setReplacement($match)
	{
		$code = '####REPLACEMENT_MARK_'.count(self::$replacements).'####';

		self::$replacements[$code] = $match[0];

		return $code;
	}

	private static function recoverReplacements($text)
	{
		foreach(self::$replacements as $code => $value)
		{
			$text = str_replace($code, $value, $text);
		}
		self::$replacements = Array();

		return $text;
	}
	
	/* 
		MESSAGE PARAMS 
		$parser->allow = array(
			"ANCHOR" => "N",
			"SMILES" => "N",
		);
	
		$message['MESSAGE'] = str_replace('<br />', ' ', $CCTP->convertText($message['MESSAGE']));
		$message['MESSAGE'] = preg_replace("/\[s\].*?\[\/s\]/i", "", $message['MESSAGE']);
		$message['MESSAGE'] = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $message['MESSAGE']);
		$message['MESSAGE'] = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $message['MESSAGE']);
		$message['MESSAGE'] = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $message['MESSAGE']);
		$message['MESSAGE'] = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $message['MESSAGE']);
		$message['MESSAGE'] = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $message['MESSAGE']);
		$message['MESSAGE'] = preg_replace("/------------------------------------------------------(.*)------------------------------------------------------/mi", " [".GetMessage('IM_QUOTE')."] ", str_replace(array("#BR#"), Array(" "), $message['MESSAGE']));

		$params['MESSAGE'] = preg_replace("/\[s\].*?\[\/s\]/i", "-", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\\[url\\](.*?)\\[\\/url\\]/i".BX_UTF_PCRE_MODIFIER, "$1", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixs".BX_UTF_PCRE_MODIFIER, "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[ATTACH=([0-9]{1,})\]/i", " [".GetMessage('IM_MESSAGE_ATTACH')."] ", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace_callback("/\[ICON\=([^\]]*)\]/i", Array("CIMmessenger", "PrepareMessageForPushIconCallBack"), $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace('#\-{54}.+?\-{54}#s', " [".GetMessage('IM_QUOTE')."] ", str_replace(array("#BR#"), Array(" "), $params['MESSAGE']));

		$quoteMessage['MESSAGE'] = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $quoteMessage['MESSAGE']);
		$quoteMessage['MESSAGE'] = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $quoteMessage['MESSAGE']);
		$quoteMessage['MESSAGE'] = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $quoteMessage['MESSAGE']);
	*/
}