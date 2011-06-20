<?php

/**
 * Jyxo PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Mail;

/**
 * Mail parsing class.
 * Based on \Mail\IMAPv2 class (c) Copyright 2004-2005 Richard York
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Parser
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Parser
{
	/**
	 * Retrieve message body.
	 * Search for possible alternatives.
	 *
	 * @var integer
	 * @see \Jyxo\Mail\Parser::getBody()
	 */
	const BODY = 0;

	/**
	 * Retrieve body info.
	 *
	 * @var integer
	 * @see \Jyxo\Mail\Parser::getBody()
	 */
	const BODY_INFO = 1;

	/**
	 * Retrieve raw message body.
	 *
	 * @var integer
	 * @see \Jyxo\Mail\Parser::getBody()
	 */
	const BODY_LITERAL = 2;

	/**
	 * Retrieve decoded message body.
	 *
	 * @var integer
	 * @see \Jyxo\Mail\Parser::getBody()
	 */
	const BODY_LITERAL_DECODE = 3;

	/**
	 * IMAP folder connection.
	 *
	 * @var resource
	 */
	private $connection = null;

	/**
	 * Message Id.
	 *
	 * @var integer
	 */
	private $uid = null;

	/**
	 * Message structure.
	 *
	 * @var array
	 */
	private $structure = array();

	/**
	 * Default part Id.
	 *
	 * @var integer
	 */
	private $defaultPid = null;

	/**
	 * Message parts (attachments and inline parts).
	 *
	 * @var array
	 */
	private $parts = array();

	/**
	 * List of part types.
	 *
	 * @var array
	 */
	private static $dataTypes = array(
		TYPETEXT => 'text',
		TYPEMULTIPART => 'multipart',
		TYPEMESSAGE => 'message',
		TYPEAPPLICATION => 'application',
		TYPEAUDIO => 'audio',
		TYPEIMAGE => 'image',
		TYPEVIDEO => 'video',
		TYPEMODEL => 'model',
		TYPEOTHER => 'other'
	);

	/**
	 * List of encodings.
	 *
	 * @var array
	 */
	private static $encodingTypes = array(
		ENC7BIT => '7bit',
		ENC8BIT => '8bit',
		ENCBINARY => 'binary',
		ENCBASE64 => 'base64',
		ENCQUOTEDPRINTABLE => 'quoted-printable',
		ENCOTHER => 'other',
		6 => 'other'
	);

	/**
	 * Creates an instance.
	 *
	 * @param resource $connection IMAP folder connection.
	 * @param integer $uid Message Id
	 */
	public function __construct($connection, $uid)
	{
		$this->connection = $connection;
		$this->uid = (int) $uid;
	}

	/**
	 * Parses a message if not already parsed.
	 *
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	private function checkIfParsed()
	{
		try {
			if (empty($this->structure)) {
				$this->setStructure();
			}

			if (empty($this->defaultPid)) {
				$this->defaultPid = $this->getDefaultPid();
			}
		} catch (\Jyxo\Mail\Parser\EmailNotExistException $e) {
			throw $e;
		}
	}

	/**
	 * Creates message structure.
	 *
	 * @param array $subparts Subparts
	 * @param string $parentPartId Parent Id
	 * @param boolean $skipPart Skip parts
	 * @param boolean $lastWasSigned Was the pared signed
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	private function setStructure(array $subparts = null, $parentPartId = null, $skipPart = false, $lastWasSigned = false)
	{
		// First call - an object returned by the imap_fetchstructure function is returned
		if (null === $subparts) {
			$this->structure['obj'] = imap_fetchstructure($this->connection, $this->uid, FT_UID);
			if (!$this->structure['obj']) {
				throw new Parser\EmailNotExistException('Email does not exist');
			}
		}

		// Sometimes (especially in spams) the type is missing
		if (empty($this->structure['obj']->type)) {
			$this->structure['obj']->type = TYPETEXT;
		}

		// For situations when the body is missing but we have attachments
		if (($this->structure['obj']->type != TYPETEXT)
				&& ($this->structure['obj']->type != TYPEMULTIPART)) {
			$temp = $this->structure['obj'];

			// Don't add a body just create the multipart container because the body wouldn't have an Id
			$this->structure['obj'] = new \stdClass();
			$this->structure['obj']->type = TYPEMULTIPART;
			$this->structure['obj']->ifsubtype = 1;
			$this->structure['obj']->subtype = 'MIXED';
			$this->structure['obj']->ifdescription = 0;
			$this->structure['obj']->ifid = 0;
			$this->structure['obj']->bytes = isset($temp->bytes) ? $temp->bytes : 0;
			$this->structure['obj']->ifdisposition = 1;
			$this->structure['obj']->disposition = 'inline';
			$this->structure['obj']->ifdparameters = 0;
			$this->structure['obj']->dparameters = array();
			$this->structure['obj']->ifparameters = 0;
			$this->structure['obj']->parameters = array();
			$this->structure['obj']->parts = array($temp);
		}

		// Deals a multipart/alternative or multipart/report problem when they are as the first part
		if ((null === $subparts) && (null === $parentPartId)) {
			$ftype = empty($this->structure['obj']->type)
				? $this->getMajorMimeType(0) . '/' . strtolower($this->structure['obj']->subtype)
				: $this->getMajorMimeType($this->structure['obj']->type) . '/' . strtolower($this->structure['obj']->subtype);

			// As first they do not have any actual Id, assign a fake one 0
			// if (('multipart/alternative' == $ftype) || ('multipart/report' == $ftype)) {
				$this->structure['pid'][0] = 0;
				$this->structure['ftype'][0] = $ftype;
				$this->structure['encoding'][0] = !empty($this->structure['obj']->encoding) ? self::$encodingTypes[$this->structure['obj']->encoding] : self::$encodingTypes[0];
				$this->structure['fsize'][0] = !empty($this->structure['obj']->bytes) ? $this->structure['obj']->bytes : 0;
				$this->structure['disposition'][0] = 'inline';
			// }
		}

		// Subparts
		if (isset($this->structure['obj']->parts) || is_array($subparts)) {
			if (is_array($subparts)) {
				$parts = $subparts;
			} else {
				$parts = $this->structure['obj']->parts;
			}

			$count = 1;
			foreach ($parts as $part) {
				// Skips multipart/mixed, following multipart/alternative or multipart/report (if this part is message/rfc822), multipart/related
				// There are more problematic parts but we haven't tested them yet
				$ftype = empty($part->type)
					? $this->getMajorMimeType(0) . '/' . strtolower($part->subtype)
					: $this->getMajorMimeType($part->type) . '/' . strtolower($part->subtype);

				$thisIsSigned = ('multipart/signed' == $ftype);
				$skipNext = ('message/rfc822' == $ftype);

				$no = isset($this->structure['pid']) ? count($this->structure['pid']) : 0;

				// Skip parts fulfilling certain conditions
				if ((('multipart/mixed' == $ftype) && ($lastWasSigned || $skipPart))
						|| ('multipart/signed' == $ftype)
						|| ($skipPart && ('multipart/alternative' == $ftype))
						|| ($skipPart && ('multipart/report' == $ftype))
						|| (('multipart/related' == $ftype) && (1 === count($parts)))) {
					$skipped = true;

					// Although this part is skipped, save is for later use (as Id we use the parent Id)
					$this->structure['pid'][$no] = $parentPartId;
					$this->structure['ftype'][$no] = $ftype;
					$this->structure['encoding'][$no] = !empty($this->structure['obj']->encoding) ? self::$encodingTypes[$this->structure['obj']->encoding] : self::$encodingTypes[0];
					$this->structure['fsize'][$no] = !empty($this->structure['obj']->bytes) ? $this->structure['obj']->bytes : 0;
					$this->structure['disposition'][$no] = 'inline';
				} else {
					$skipped = false;

					$this->structure['pid'][$no] = (!is_array($subparts)) ? strval($count) : $parentPartId . '.' . $count;
					$this->structure['ftype'][$no] = $ftype;
					$this->structure['encoding'][$no] = !empty($part->encoding) ? self::$encodingTypes[$part->encoding] : self::$encodingTypes[0];
					$this->structure['fsize'][$no] = !empty($part->bytes) ? $part->bytes : 0;

					// Loads parameters
					if ($part->ifdparameters) {
						foreach ($part->dparameters as $param) {
							$this->structure[strtolower($param->attribute)][$no] = strtolower($param->value);
						}
					}
					if ($part->ifparameters) {
						foreach ($part->parameters as $param) {
							$this->structure[strtolower($param->attribute)][$no] = strtolower($param->value);
						}
					}

					// Builds a part name (can be split into multiple lines)
					if ($part->ifparameters) {
						foreach ($part->parameters as $param) {
							if (0 === stripos($param->attribute, 'name')) {
								if (!isset($this->structure['fname'][$no])) {
									$this->structure['fname'][$no] = $param->value;
								} else {
									$this->structure['fname'][$no] .= $param->value;
								}
							}
						}
					}
					if (($part->ifdparameters)
							&& ((!isset($this->structure['fname'][$no])) || (empty($this->structure['fname'][$no])))) {
						foreach ($part->dparameters as $param) {
							if (0 === stripos($param->attribute, 'filename')) {
								if (!isset($this->structure['fname'][$no])) {
									$this->structure['fname'][$no] = $param->value;
								} else {
									$this->structure['fname'][$no] .= $param->value;
								}
							}
						}
					}
					// If a name exists, decode it
					if (isset($this->structure['fname'][$no])) {
						$this->structure['fname'][$no] = $this->decodeFilename($this->structure['fname'][$no]);
					}

					// If the given part is message/rfc822, load its headers and use the subject as its name
					if ('message/rfc822' == $ftype) {
						$rfcHeader = $this->getHeaders($this->structure['pid'][$no]);
						$this->structure['fname'][$no] = !empty($rfcHeader['subject']) ? $rfcHeader['subject'] . '.eml' : '';
					}

					// Part Id
					if ($part->ifid) {
						$this->structure['cid'][$no] = substr($part->id, 1, -1);
					}

					// Attachment or inline part (sometimes we do not get the required information from the message or it's nonsense)
					list($type, $subtype) = explode('/', $ftype);
					if (($part->ifdisposition) && ('attachment' == strtolower($part->disposition))) {
						$this->structure['disposition'][$no] = 'attachment';
					} elseif ((isset($this->structure['cid'][$no])) && ('image' == $type)) {
						$this->structure['disposition'][$no] = 'inline';
					} elseif (('message' == $type) || ('application' == $type) || ('image' == $type) || ('audio' == $type)
							|| ('video' == $type) || ('model' == $type) || ('other' == $type)) {
						$this->structure['disposition'][$no] = 'attachment';
					} elseif (('text' == $type) && (('html' != $subtype) && ('plain' != $subtype))) {
						$this->structure['disposition'][$no] = 'attachment';
					} elseif (('text' == $type) && (isset($this->structure['fname'][$no]))) {
						$this->structure['disposition'][$no] = 'attachment';
					} else {
						$this->structure['disposition'][$no] = 'inline';
					}
				}

				if ((isset($part->parts)) && (is_array($part->parts))) {
					if (!$skipped) {
						$this->structure['hasAttach'][$no] = true;
					}

					$this->setStructure($part->parts, end($this->structure['pid']), $skipNext, $thisIsSigned);
				} elseif (!$skipped) {
					$this->structure['hasAttach'][$no] = false;
				}

				$count++;
			}
		} else {
			// No subparts

			$this->structure['pid'][0] = 1;

			$this->structure['ftype'][0] = $this->getMajorMimeType($this->structure['obj']->type) . '/' . strtolower($this->structure['obj']->subtype);

			// If the message has only one part it should be text/plain or text/html
			if (($this->structure['ftype'][0] != 'text/plain') && ($this->structure['ftype'][0] != 'text/html')) {
				$this->structure['ftype'][0] = 'text/plain';
			}

			if (empty($this->structure['obj']->encoding)) {
				$this->structure['obj']->encoding = 0;
			}

			$this->structure['encoding'][0] = self::$encodingTypes[$this->structure['obj']->encoding];

			if (isset($this->structure['obj']->bytes)) {
				$this->structure['fsize'][0] = $this->structure['obj']->bytes;
			}

			$this->structure['disposition'][0] = 'inline';
			$this->structure['hasAttach'][0] = false;

			// Walks through next parameters
			if ((isset($this->structure['obj']->ifparameters)) && ($this->structure['obj']->ifparameters)) {
				foreach ($this->structure['obj']->parameters as $param) {
					$this->structure[strtolower($param->attribute)][0] = $param->value;
				}
			}
		}
	}

	/**
	 * Returns default part's Id.
	 *
	 * @param string $mimeType Mime-type
	 * @param integer $attempt Number of retries
	 * @return integer
	 */
	private function getDefaultPid($mimeType = 'text/html', $attempt = 1)
	{
		$mimeCheck = ('text/html' == $mimeType) ? array('text/html', 'text/plain') : array('text/plain', 'text/html');

		// Tries to find text/html or text/plain in main parts
		foreach ($mimeCheck as $mime) {
			$parts = array_keys($this->structure['ftype'], $mime);
			foreach ($parts as $part) {
				if (('inline' == $this->structure['disposition'][$part])
						&& (false === strpos($this->structure['pid'][$part], '.'))) {
					return $this->structure['pid'][$part];
				}
			}
		}

		// There was nothing found in the main parts, try multipart/alternative or multipart/report
		$partLevel = 1;
		$pidLength = 1;
		foreach ($this->structure['pid'] as $partNo => $pid) {
			$level = count(explode('.', $pid));

			if (!isset($multipartPid)) {
				if ((1 === $level) && (isset($this->structure['ftype'][$partNo])) && ($this->isPartMultipart($partNo, 'related'))) {
					$partLevel = 2;
					$pidLength = 3;
					continue;
				}

				if (($level == $partLevel) && (isset($this->structure['ftype'][$partNo]))
						&& (($this->isPartMultipart($partNo, 'alternative'))
						|| ($this->isPartMultipart($partNo, 'report')) || ($this->isPartMultipart($partNo, 'mixed')))) {
					$multipartPid = $pid;
					continue;
				}
			}

			if ((isset($multipartPid)) && ($level == ($partLevel + 1))
					&& ($this->structure['ftype'][$partNo] == $mimeType) && ($multipartPid == substr($pid, 0, $pidLength))) {
				return $pid;
			}
		}

		// Nothing was found, try next possible type
		if (1 === $attempt) {
			if ('text/html' == $mimeType) {
				return $this->getDefaultPid('text/plain', 2);
			} else {
				return $this->getDefaultPid('text/html', 2);
			}
		} else {
			// There should be a default part found in every mail; this is because of spams that are often in wrong format
			return 1;
			// throw new \Jyxo\Mail\Parser\DefaultPartIdNotExistException('Default part Id does no exist.');
		}
	}

	/**
	 * Returns headers.
	 *
	 * @param string $pid Part Id
	 * @return array
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	public function getHeaders($pid = null)
	{
		// Parses headers
		$rawHeaders = $this->getRawHeaders($pid);
		if (null === $pid) {
			$msgno = imap_msgno($this->connection, $this->uid);
			if (0 === $msgno) {
				throw new Parser\EmailNotExistException('Email does not exist');
			}
			$headerInfo = imap_headerinfo($this->connection, $msgno);
		} else {
			$headerInfo = imap_rfc822_parse_headers($rawHeaders);
		}

		// Adds a header that the IMAP extension does not support
		if (preg_match("~Disposition-Notification-To:(.+?)(?=\r?\n(?:\S|\r?\n))~is", $rawHeaders, $matches)) {
			$addressList = imap_rfc822_parse_adrlist($matches[1], '');
			// {''} is used because of CS rules
			$headerInfo->{'disposition_notification_toaddress'} = substr(trim($matches[1]), 0, 1024);
			$headerInfo->{'disposition_notification_to'} = array($addressList[0]);
		}

		$headers = array();
		static $mimeHeaders = array(
			'toaddress', 'ccaddress', 'bccaddress', 'fromaddress', 'reply_toaddress', 'senderaddress',
			'return_pathaddress', 'subject', 'fetchfrom', 'fetchsubject', 'disposition_notification_toaddress'
		);
		foreach ($headerInfo as $key => $value) {
			if ((!is_object($value)) && (!is_array($value))) {
				if (in_array($key, $mimeHeaders)) {
					$headers[$key] = $this->decodeMimeHeader($value);
				} else {
					$headers[$key] = $this->convertToUtf8($value);
				}
			}
		}

		// Adds "udate" if missing
		if (!empty($headerInfo->udate)) {
			$headers['udate'] = $headerInfo->udate;
		} elseif (!empty($headerInfo->date)) {
			$headers['udate'] = strtotime($headerInfo->date);
		} else {
			$headers['udate'] = time();
		}

		// Parses references
		$headers['references'] = explode('> <', trim($headers['references'], '<>'));

		static $types = array('to', 'cc', 'bcc', 'from', 'reply_to', 'sender', 'return_path', 'disposition_notification_to');
		for ($i = 0; $i < count($types); $i++) {
			$type = $types[$i];
			$headers[$type] = array();
			if (isset($headerInfo->$type)) {
				foreach ($headerInfo->$type as $object) {
					$newHeader = array();
					foreach ($object as $attributeName => $attributeValue) {
						if (!empty($attributeValue)) {
							$newHeader[$attributeName] = ('personal' === $attributeName)
								? $this->decodeMimeHeader($attributeValue)
								: $this->convertToUtf8($attributeValue);
						}
					}

					if (!empty($newHeader)) {
						if (isset($newHeader['mailbox'], $newHeader['host'])) {
							$newHeader['email'] = $newHeader['mailbox'] . '@' . $newHeader['host'];
						} elseif (isset($newHeader['mailbox'])) {
							$newHeader['email'] = $newHeader['mailbox'];
						} else {
							$newHeader['email'] = 'undisclosed-recipients';
						}

						$headers[$type][] = $newHeader;
					}
				}
			}
		}

		// Adds X-headers
		if (preg_match_all("~(X(?:[-]\w+)+):(.+?)(?=\r?\n(?:\S|\r?\n))~is", $rawHeaders, $matches) > 0) {
			for ($i = 0; $i < count($matches[0]); $i++) {
				// Converts to the format used by imap_headerinfo()
				$key = str_replace('-', '_', strtolower($matches[1][$i]));
				// Removes line endings
				$value = strtr(trim($matches[2][$i]), array("\r" => '', "\n" => '', "\t" => ' '));
				$headers[$key] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Returns raw headers.
	 *
	 * @param string $pid Part Id
	 * @return string
	 * @throws \Jyxo\Mail\Parser\PartNotExistException If there is no such
	 */
	private function getRawHeaders($pid = null)
	{
		if (null === $pid) {
			return imap_fetchheader($this->connection, $this->uid, FT_UID);
		}

		$rawHeaders = imap_fetchbody($this->connection, $this->uid, $pid, FT_UID);
		if (empty($rawHeaders)) {
			throw new Parser\PartNotExistException('No such part exists.');
		}

		$headersEnd = (false !== strpos($rawHeaders, "\n\n"))
			? strpos($rawHeaders, "\n\n")
			: strpos($rawHeaders, "\n\r\n");

		return substr($rawHeaders, 0, $headersEnd);
	}

	/**
	 * Parses message body.
	 *
	 * @param string $pid Part Id
	 * @param string $mimeType Default mime-type
	 * @param boolean $alternative Should the alternative part be used as well
	 * @param boolean $all Should all parts get parsed
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	public function parseBody($pid = null, $mimeType = 'text/html', $alternative = true, $all = false)
	{
		try {
			$this->checkIfParsed();
		} catch (\Jyxo\Mail\Parser\EmailNotExistException $e) {
			throw $e;
		}

		if (null === $pid) {
			$pid = $this->defaultPid;
		}

		// If only one part exists, it is already parsed
		if (count($this->structure['pid']) > 1) {
			$key = array_search($pid, $this->structure['pid']);
			if (false !== $key) {
				if ($all) {
					$this->parseMultiparts($pid, $mimeType, 'all', 2, $alternative);
				} else {
					if ($pid == $this->defaultPid) {
						$this->parseMultiparts($pid, $mimeType, 'top', 2, $alternative);
					} elseif ('message/rfc822' == $this->structure['ftype'][$i]) {
						$this->parseMultiparts($pid, $mimeType, 'subparts', 1, $alternative);
					}
				}
			}
		}
	}

	/**
	 * Parses multiple parts.
	 *
	 * @param string $pid Part Id
	 * @param string $mimeType Default mime-type
	 * @param string $lookFor What parts to look for
	 * @param integer $pidAdd The level of nesting
	 * @param boolean $getAlternative Should the alternative part be used as well
	 */
	private function parseMultiparts($pid, $mimeType, $lookFor = 'all', $pidAdd = 1, $getAlternative = true)
	{
		// If the type is message/rfc822, gathers subparts that begin with the same Id
		// Skips multipart/alternative or multipart/report
		$excludeMime = $mimeType;
		$mimeType = ('text/plain' == $excludeMime) ? 'text/html' : 'text/plain';

		$partLevel = count(explode('.', $pid));
		$pidLength = strlen($pid);
		foreach ($this->structure['pid'] as $partNo => $id) {
			$level = count(explode('.', $this->structure['pid'][$partNo]));

			switch ($lookFor) {
				case 'all':
					$condition = true;
					break;
				case 'subparts':
					$condition = (($level == ($partLevel + 1)) &&  ($pid == substr($this->structure['pid'][$partNo], 0, $pidLength)));
					break;
				case 'top':
					// Break missing intentionally
				default:
					if ($this->isMultipart('related') || $this->isMultipart('mixed')) {
						// Top level and second level, but the same parent
						$condition = ((false === strpos($this->structure['pid'][$partNo], '.'))
							|| ((2 == $level) && substr($this->defaultPid, 0, 1) == substr($this->structure['pid'][$partNo], 0, 1)));
					} else {
						// Top level
						$condition = (false === strpos($this->structure['pid'][$partNo], '.'));
					}
					break;
			}

			if ($condition) {
				if (($this->isPartMultipart($partNo, 'alternative')) || ($this->isPartMultipart($partNo, 'report')) || ($this->isPartMultipart($partNo, 'mixed'))) {
					$subLevel = count(explode('.', $this->structure['pid'][$partNo]));
					foreach ($this->structure['pid'] as $multipartNo => $multipartPid) {
						// Part must begin with the last tested Id and be in the next level
						if (($this->structure['ftype'][$multipartNo] == $mimeType) && $getAlternative && ($subLevel == ($partLevel + $pidAdd))
								&& ($pid == substr($multipartPid, 0, strlen($this->structure['pid'][$partNo])))) {
							$this->addPart($partNo, 'inline');
							break;
						}
					}
				} elseif (('inline' == $this->structure['disposition'][$partNo])
						&& (!$this->isPartMultipart($partNo, 'related')) && (!$this->isPartMultipart($partNo, 'mixed'))) {
					// It is inline, but not related or mixed type

					if ((($this->structure['ftype'][$partNo] != $excludeMime) && ($pid != $this->structure['pid'][$partNo]) && ($getAlternative || !$this->isParentAlternative($partNo)))
							|| (($this->structure['ftype'][$partNo] == $excludeMime) && (isset($this->structure['fname'][$partNo])) && ($pid != $this->structure['pid'][$partNo]))) {
						$this->addPart($partNo, 'inline');
					}
				} elseif ('attachment' == $this->structure['disposition'][$partNo]) {
					// It is an attachment; add to the attachment list

					$this->addPart($partNo, 'attach');
				}
			}
		}
	}

	/**
	 * Returns if the parent is multipart/alternative type.
	 *
	 * @param integer $partNo Part Id
	 * @return boolean
	 */
	private function isParentAlternative($partNo)
	{
		// Multipart/alternative can be a child of only two types
		if (($this->structure['ftype'][$partNo] != 'text/plain') && ($this->structure['ftype'][$partNo] != 'text/plain')) {
			return false;
		}

		$partId = $this->structure['pid'][$partNo];
		$partLevel = count(explode('.', $partId));
		if (1 === $partLevel) {
			return $this->isPartMultipart(0, 'alternative');
		}

		$parentId = substr($partId, 0, strrpos($partId, '.'));
		for ($i = 0; $i < count($this->structure['pid']); $i++) {
			// There can be multiple parts with the same Id (because we assign parent Id to parts without an own Id)
			if (($parentId == $this->structure['pid'][$i]) && ($this->isPartMultipart($i, 'alternative'))) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns if the message is multipart/subtype.
	 *
	 * @param string $subtype Subtype
	 * @return boolean
	 */
	private function isMultipart($subtype)
	{
		return (count($this->getMime(array('multipart/' . $subtype))) > 0);
	}

	/**
	 * Returns if the given part is is multipart/subtype.
	 *
	 * @param integer $partNo Part Id
	 * @param string $subtype Subtype
	 * @return boolean
	 */
	private function isPartMultipart($partNo, $subtype)
	{
		return ($this->structure['ftype'][$partNo] == ('multipart/' . $subtype));
	}

	/**
	 * Adds a part to the list.
	 *
	 * @param integer $structureNo Part Id in the structure
	 * @param string $partType Part type
	 */
	private function addPart($structureNo, $partType)
	{
		$fields = array('fname', 'pid', 'ftype', 'fsize', 'hasAttach', 'charset');

		$no = isset($this->parts[$partType]['pid']) ? count($this->parts[$partType]['pid']) : 0;
		foreach ($fields as $field) {
			if (!empty($this->structure[$field][$structureNo])) {
				$this->parts[$partType][$field][$no] = $this->structure[$field][$structureNo];
			}
		}
	}

	/**
	 * Returns a part Id.
	 *
	 * @param string $pid Parent Id
	 * @param string $mimeType Requested mime-type
	 * @param string $lookFor What to look for
	 * @return string
	 */
	private function getMultipartPid($pid, $mimeType, $lookFor)
	{
		$partLevel = count(explode('.', $pid));
		$pidLength = strlen($pid);
		$pidAdd = 1;
		foreach ($this->structure['pid'] as $partNo => $id) {
			$level = count(explode('.', $this->structure['pid'][$partNo]));

			switch ($lookFor) {
				case 'subparts':
					$condition = (($level == ($partLevel + 1)) &&  ($pid == substr($this->structure['pid'][$partNo], 0, $pidLength)));
					break;
				case 'multipart':
					$condition = (($level == ($partLevel + 1)) && ($pid == substr($this->structure['pid'][$partNo])));
					break;
				default:
					$condition = false;
					break;
			}

			if ($condition) {
				if (($this->isPartMultipart($partNo, 'alternative')) || ($this->isPartMultipart($partNo, 'report')) || ($this->isPartMultipart($partNo, 'mixed'))) {
					foreach ($this->structure['pid'] as $multipartNo => $multipartPid) {
						// Part has to begin with the last tested Id and has to be in the next level
						$subLevel = count(explode('.', $this->structure['pid'][$partNo]));

						if (($this->structure['ftype'][$multipartNo] == $mimeType) && ($subLevel == ($partLevel + $pidAdd))
								&& ($pid == substr($multipartPid, 0, strlen($this->structure['pid'][$partNo])))) {
							if (empty($this->structure['fname'][$multipartNo])) {
								return $this->structure['pid'][$multipartNo];
							}
						} elseif (($this->isPartMultipart($multipartNo, 'alternative')) || ($this->isPartMultipart($multipartNo, 'report'))) {
							// Need to match this PID to next level in
							$pid = $this->structure['pid'][$multipartNo];
							$pidLength = strlen($pid);
							$partLevel = count(explode('.', $pid));
							$pidAdd = 2;
							continue;
						}
					}
				} elseif (('inline' == $this->structure['disposition'][$partNo])
						&& (!$this->isPartMultipart($multipartNo, 'related')) && (!$this->isPartMultipart($multipartNo, 'mixed'))) {
					// It is inline, but not related or mixed type

					if (($this->structure['ftype'][$partNo] == $mimeType) && (!isset($this->structure['fname'][$partNo]))) {
						return $this->structure['pid'][$partNo];
					}
				}
			}
		}
	}

	/**
	 * Returns a list of attachments.
	 *
	 * @return array
	 */
	public function getAttachments()
	{
		return isset($this->parts['attach']['pid']) ? $this->parts['attach']['pid'] : array();
	}

	/**
	 * Returns a list of part Ids of inline parts.
	 *
	 * @return array
	 */
	public function getInlines()
	{
		return isset($this->parts['inline']['pid']) ? $this->parts['inline']['pid'] : array();
	}

	/**
	 * Returns related parts.
	 *
	 * @param string $pid Part Id
	 * @param array $types List of types to search for
	 * @param boolean $all Return all types
	 * @return array
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	public function getRelatedParts($pid, array $types, $all = false)
	{
		try {
			$this->checkIfParsed();
		} catch (\Jyxo\Mail\Parser\EmailNotExistException $e) {
			throw $e;
		}

		$related = array();
		if (!empty($this->structure['pid'])) {
			// Deals a problem with multipart/alternative and multipart/report, when they are as the first part and don't have any real Ids (they have a fake Id 0 assigned then)
			if (0 === $pid) {
				for ($i = 1; $i < count($this->structure['pid']); $i++) {
					// Subparts do not contain a dot because they are in the first level
					if ((false === strpos($this->structure['pid'][$i], '.'))
							&& (($all) || (in_array($this->structure['ftype'][$i], $types)))) {
						$related['pid'][] = $this->structure['pid'][$i];
						$related['ftype'][] = $this->structure['ftype'][$i];
					}
				}
			} else {
				$level = count(explode('.', $pid));
				foreach ($this->structure['pid'] as $i => $rpid) {
					// Part is one level deeper and the first number equals to the parent
					if ((count(explode('.', $rpid)) == ($level + 1)) && ($pid == substr($rpid, 0, strrpos($rpid, '.')))) {
						if (($all) || (in_array($this->structure['ftype'][$i], $types))) {
							$related['pid'][] = $this->structure['pid'][$i];
							$related['ftype'][] = $this->structure['ftype'][$i];
						}
					}
				}
			}
		}

		return $related;
	}

	/**
	 * Returns all related parts.
	 *
	 * @param string $pid Part Id
	 * @return array
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	public function getAllRelatedParts($pid)
	{
		try {
			return $this->getRelatedParts($pid, array(), true);
		} catch (\Jyxo\Mail\Parser\EmailNotExistException $e) {
			throw $e;
		}
	}

	/**
	 * Returns body of the given part.
	 *
	 * @param string $pid Part Id
	 * @param integer $mode Body return mode
	 * @param string $mimeType Requested mime-type
	 * @param integer $attempt Number of retries
	 * @return array
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 * @throws \Jyxo\Mail\Parser\PartNotExistException If no such part exists
	 */
	public function getBody($pid = '1', $mode = self::BODY, $mimeType = 'text/html', $attempt = 1)
	{
		try {
			$this->checkIfParsed();
		} catch (\Jyxo\Mail\Parser\EmailNotExistException $e) {
			throw $e;
		}

		$key = array_search($pid, $this->structure['pid']);
		if (false === $key) {
			throw new Parser\PartNotExistException('Requested part does not exist');
		}

		$output['encoding'] = $this->structure['encoding'][$key];
		$output['type'] = $this->structure['ftype'][$key];
		$output['size'] = $this->structure['fsize'][$key];

		if (isset($this->structure['fname'][$key])) {
			$output['filename'] = $this->structure['fname'][$key];
		}
		if (isset($this->structure['charset'][$key])) {
			$output['charset'] = $this->structure['charset'][$key];
		}
		if (isset($this->structure['cid'][$key])) {
			$output['cid'] = $this->structure['cid'][$key];
		}

		if (self::BODY_INFO === $mode) {
			return $output;
		}

		if (self::BODY_LITERAL === $mode) {
			$output['content'] = imap_fetchbody($this->connection, $this->uid, $pid, FT_UID);
			return $output;
		}

		if (self::BODY_LITERAL_DECODE === $mode) {
			$output['content'] = self::decodeBody(imap_fetchbody($this->connection, $this->uid, $pid, FT_UID), $output['encoding']);

			// Textual types are converted to UTF-8
			if ((0 === strpos($output['type'], 'text/')) || (0 === strpos($output['type'], 'message/'))) {
				$output['content'] = $this->convertToUtf8($output['content'], isset($output['charset']) ? $output['charset'] : '');
			}

			return $output;
		}

		// Get a new part number
		if (('message/rfc822' == $this->structure['ftype'][$key]) || ($this->isPartMultipart($key, 'related'))
				|| ($this->isPartMultipart($key, 'alternative')) || ($this->isPartMultipart($key, 'report'))) {

			$newPid = ('message/rfc822' == $this->structure['ftype'][$key])
					|| ($this->isPartMultipart($key, 'related'))
					|| ($this->isPartMultipart($key, 'alternative'))
					|| ($this->isPartMultipart($key, 'report'))
				? $this->getMultipartPid($pid, $mimeType, 'subparts')
				: $this->getMultipartPid($pid, $mimeType, 'multipart');

			// If no type was found, try again
			if (!empty($newPid)) {
				$pid = $newPid;
			} elseif (empty($newPid) && ('text/html' == $mimeType)) {
				if (1 === $attempt) {
					return $this->getBody($pid, $mode, 'text/plain', 2);
				}
			} elseif (empty($newPid) && ('text/plain' == $mimeType)) {
				if (1 === $attempt) {
					return $this->getBody($pid, $mode, 'text/html', 2);
				}
			}
		}

		if (!empty($newPid)) {
			$key = array_search($pid, $this->structure['pid']);
			if (false === $key) {
				throw new Parser\PartNotExistException('Requested part does not exist');
			}
		}

		$output['encoding'] = $this->structure['encoding'][$key];
		$output['type'] = $this->structure['ftype'][$key];
		$output['size'] = $this->structure['fsize'][$key];

		if (isset($this->structure['fname'][$key])) {
			$output['filename'] = $this->structure['fname'][$key];
		}
		if (isset($this->structure['charset'][$key])) {
			$output['charset'] = $this->structure['charset'][$key];
		}

		$output['content']  = self::decodeBody(imap_fetchbody($this->connection, $this->uid, $pid, FT_UID), $output['encoding']);

		// Textual types are converted to UTF-8
		if ((0 === strpos($output['type'], 'text/')) || (0 === strpos($output['type'], 'message/'))) {
			$output['content'] = $this->convertToUtf8($output['content'], isset($output['charset']) ? $output['charset'] : '');
		}

		return $output;
	}

	/**
	 * Decodes body.
	 *
	 * @param string $body Body
	 * @param string $encoding Body encoding
	 * @return string
	 */
	public static function decodeBody($body, $encoding)
	{
		switch ($encoding) {
			case 'quoted-printable':
				return quoted_printable_decode($body);
			case 'base64':
				return imap_base64($body);
			default:
				return $body;
		}
	}

	/**
	 * Returns a list of part Ids of given types.
	 *
	 * @param array $types Part types
	 * @return array
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	public function getMime(array $types)
	{
		try {
			$this->checkIfParsed();
		} catch (\Jyxo\Mail\Parser\EmailNotExistException $e) {
			throw $e;
		}

		$parts = array();
		if (is_array($this->structure['ftype'])) {
			foreach ($types as $type) {
				foreach (array_keys($this->structure['ftype'], $type) as $key) {
					$parts[] = $this->structure['pid'][$key];
				}
			}
		}

		return $parts;
	}

	/**
	 * Returns a list of part Ids of all parts except for the given types.
	 *
	 * @param array $exceptTypes Ignored part types
	 * @return array
	 * @throws \Jyxo\Mail\Parser\EmailNotExistException If no such email exists
	 */
	public function getMimeExcept(array $exceptTypes)
	{
		try {
			$this->checkIfParsed();
		} catch (\Jyxo\Mail\Parser\EmailNotExistException $e) {
			throw $e;
		}

		$parts = array();
		if (is_array($this->structure['ftype'])) {
			$allExcept = array_diff($this->structure['ftype'], $types);
			foreach (array_keys($allExcept) as $key) {
				$parts[] = $this->structure['pid'][$key];
			}
		}

		return $parts;
	}

	/**
	 * Returns textual representation of the major mime-type.
	 *
	 * @param integer $mimetypeNo Mime-type number
	 * @return string
	 */
	private function getMajorMimeType($mimetypeNo)
	{
		if (isset(self::$dataTypes[$mimetypeNo])) {
			return self::$dataTypes[$mimetypeNo];
		}

		// Type other
		return self::$dataTypes[max(array_keys(self::$dataTypes))];
	}

	/**
	 * Decodes given header.
	 *
	 * @param string $header Header contents
	 * @return string
	 */
	private function decodeMimeHeader($header)
	{
		$headerDecoded = imap_mime_header_decode($header);
		// Decode failed
		if (false === $headerDecoded) {
			return trim($header);
		}

		$header = '';
		for ($i = 0; $i < count($headerDecoded); $i++) {
			$header .= $this->convertToUtf8($headerDecoded[$i]->text, $headerDecoded[$i]->charset);
		}
		return trim($header);
	}

	/**
	 * Decodes attachment's name.
	 *
	 * @param string $filename Filename
	 * @return string
	 */
	private function decodeFilename($filename)
	{
		if (preg_match('~(?P<charset>[^\']+)\'(?P<lang>[^\']*)\'(?P<filename>.+)~i', $filename, $parts)) {
			$filename = $this->convertToUtf8(rawurldecode($parts['filename']), $parts['charset']);
		} elseif (0 === strpos($filename, '=?')) {
			$filename = $this->decodeMimeHeader($filename);
		}
		return $filename;
	}

	/**
	 * Converts a string from various encodings to UTF-8.
	 *
	 * @param string $string Input string
	 * @param string $charset String charset
	 * @return string
	 */
	private function convertToUtf8($string, $charset = '')
	{
		// Imap_mime_header_decode returns "default" in case of ASCII, but we make a detection for sure
		if ('default' === $charset || 'us-ascii' === $charset || empty($charset)) {
			$charset = \Jyxo\Charset::detect($string);
		}

		return \Jyxo\Charset::convert2utf($string, $charset);
	}
}
