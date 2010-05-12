<?php
/**
 * Handle MeetingResponse requests.
 * 
 * Logic adapted from Z-Push, original copyright notices below.
 *
 * Copyright 2009 - 2010 The Horde Project (http://www.horde.org)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Horde_ActiveSync
 */
/**
 * Zarafa Deutschland GmbH, www.zarafaserver.de
 * This file is distributed under GPL v2.
 * Consult LICENSE file for details
 */
// MeetingResponse
define("SYNC_MEETINGRESPONSE_CALENDARID","MeetingResponse:CalendarId");
define("SYNC_MEETINGRESPONSE_FOLDERID","MeetingResponse:FolderId");
define("SYNC_MEETINGRESPONSE_MEETINGRESPONSE","MeetingResponse:MeetingResponse");
define("SYNC_MEETINGRESPONSE_REQUESTID","MeetingResponse:RequestId");
define("SYNC_MEETINGRESPONSE_REQUEST","MeetingResponse:Request");
define("SYNC_MEETINGRESPONSE_RESULT","MeetingResponse:Result");
define("SYNC_MEETINGRESPONSE_STATUS","MeetingResponse:Status");
define("SYNC_MEETINGRESPONSE_USERRESPONSE","MeetingResponse:UserResponse");
define("SYNC_MEETINGRESPONSE_VERSION","MeetingResponse:Version");

class Horde_ActiveSync_Request_MeetingResponse extends Horde_ActiveSync_Request_Base
{
    /**
     * Handle request
     *
     * @return boolean
     */
    public function handle()
    {
        $requests = Array();
        if (!$this->_decoder->getElementStartTag(SYNC_MEETINGRESPONSE_MEETINGRESPONSE)) {
            return false;
        }

        while ($this->_decoder->getElementStartTag(SYNC_MEETINGRESPONSE_REQUEST)) {
            $req = Array();

            if ($this->_decoder->getElementStartTag(SYNC_MEETINGRESPONSE_USERRESPONSE)) {
                $req['response'] = $this->_decoder->getElementContent();
                if (!$this->_decoder->getElementEndTag()) {
                    return false;
                }
            }

            if ($this->_decoder->getElementStartTag(SYNC_MEETINGRESPONSE_FOLDERID)) {
                $req['folderid'] = $this->_decoder->getElementContent();
                if (!$this->_decoder->getElementEndTag()) {
                    return false;
                }
            }

            if ($this->_decoder->getElementStartTag(SYNC_MEETINGRESPONSE_REQUESTID)) {
                $req['requestid'] = $this->_decoder->getElementContent();
                if (!$this->_decoder->getElementEndTag()) {
                    return false;
                }
            }

            if (!$this->_decoder->getElementEndTag()) {
                return false;
            }

            array_push($requests, $req);
        }

        if (!$this->_decoder->getElementEndTag()) {
            return false;
        }

        // Start output, simply the error code, plus the ID of the calendar item that was generated by the
        // accept of the meeting response
        $this->_encoder->StartWBXML();
        $this->_encoder->startTag(SYNC_MEETINGRESPONSE_MEETINGRESPONSE);

        foreach ($requests as $req) {
            $calendarid = '';
            $ok = $this->_driver->MeetingResponse($req['requestid'], $req['folderid'], $req['response'], $calendarid);
            $this->_encoder->startTag(SYNC_MEETINGRESPONSE_RESULT);
            $this->_encoder->startTag(SYNC_MEETINGRESPONSE_REQUESTID);
            $this->_encoder->content($req['requestid']);
            $this->_encoder->endTag();
            $this->_encoder->startTag(SYNC_MEETINGRESPONSE_STATUS);
            $this->_encoder->content($ok ? 1 : 2);
            $this->_encoder->endTag();
            if ($ok) {
                $this->_encoder->startTag(SYNC_MEETINGRESPONSE_CALENDARID);
                $this->_encoder->content($calendarid);
                $this->_encoder->endTag();
            }
            $this->_encoder->endTag();
        }

        $this->_encoder->endTag();

        return true;
}