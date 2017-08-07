<?php

namespace go1\util\text;

use go1\clients\S3Client;

class IcalEvent
{
    public static function generateIcalEvent(S3Client $s3Client, $fromName, $fromAddress, $toName, $toAddress, $startTime, $endTime, $subject, $location, $portalTitle)
    {
        $vCal = 'BEGIN:VCALENDAR' . "\r\n" .
            'PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN' . "\r\n" .
            'VERSION:2.0' . "\r\n" .
            'METHOD:REQUEST' . "\r\n" .
            'BEGIN:VTIMEZONE' . "\r\n" .
            'TZID:Eastern Time' . "\r\n" .
            'BEGIN:STANDARD' . "\r\n" .
            'DTSTART:20091101T020000' . "\r\n" .
            'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11' . "\r\n" .
            'TZOFFSETFROM:-0400' . "\r\n" .
            'TZOFFSETTO:-0500' . "\r\n" .
            'TZNAME:EST' . "\r\n" .
            'END:STANDARD' . "\r\n" .
            'BEGIN:DAYLIGHT' . "\r\n" .
            'DTSTART:20090301T020000' . "\r\n" .
            'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3' . "\r\n" .
            'TZOFFSETFROM:-0500' . "\r\n" .
            'TZOFFSETTO:-0400' . "\r\n" .
            'TZNAME:EDST' . "\r\n" .
            'END:DAYLIGHT' . "\r\n" .
            'END:VTIMEZONE' . "\r\n" .
            'BEGIN:VEVENT' . "\r\n" .
            'ORGANIZER;CN="' . $fromName . '":MAILTO:' . $fromAddress . "\r\n" .
            'ATTENDEE;CN="' . $toName . '";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:' . $toAddress . "\r\n" .
            'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
            'UID:' . date("Ymd\TGis", strtotime($startTime)) . rand() . "@" . $portalTitle . "\r\n" .
            'DTSTAMP:' . date("Ymd\TGis") . "\r\n" .
            'DTSTART;TZID="Eastern Time":' . date("Ymd\THis", strtotime($startTime)) . "\r\n" .
            'DTEND;TZID="Eastern Time":' . date("Ymd\THis", strtotime($endTime)) . "\r\n" .
            'TRANSP:OPAQUE' . "\r\n" .
            'SEQUENCE:1' . "\r\n" .
            'SUMMARY:' . $subject . "\r\n" .
            'LOCATION:' . $location . "\r\n" .
            'CLASS:PUBLIC' . "\r\n" .
            'PRIORITY:5' . "\r\n" .
            'BEGIN:VALARM' . "\r\n" .
            'TRIGGER:-PT15M' . "\r\n" .
            'ACTION:DISPLAY' . "\r\n" .
            'DESCRIPTION:Reminder' . "\r\n" .
            'END:VALARM' . "\r\n" .
            'END:VEVENT' . "\r\n" .
            'END:VCALENDAR' . "\r\n";

        // Create mail attach temp directories
        $tmpDir = sys_get_temp_dir() . '/' . 'events';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
        }

        // Store mail attach to temp directory
        $tempMailPath = $tmpDir . '/' . 'event-' . time() . '.ics';
        $handle = fopen($tempMailPath, 'w');
        fwrite($handle, $vCal);
        fclose($handle);

        $fileName = parse_url(basename($tempMailPath), PHP_URL_PATH);

        return $s3Client->uploadFile($portalTitle, $tempMailPath, $fileName);
    }
}
