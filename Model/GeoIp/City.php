<?php

/* geoipcity.inc
 *
 * Copyright (C) 2004 Maxmind LLC
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
 */

/*
 * Changelog:
 *
 * 2005-01-13   Andrew Hill, Awarez Ltd. (http://www.awarez.net)
 *              Formatted file according to PEAR library standards.
 *              Changed inclusion of geoip.inc file to require_once, so that
 *                  this library can be used in the same script as geoip.inc.
 *
 * Changelog:
 *
 * 2010-07-14   GoMage (https://www.gomage.com)
 *              Changed for the GoMage LightCheckout Extension
 */



namespace GoMage\LightCheckout\Model\GeoIp;

class City
{
    const FULL_RECORD_LENGTH = 50;
    static function getrecordwithdnsservice($str)
    {

        $record = new Dnsrecord;
        $keyvalue = split(";", $str);
        foreach ($keyvalue as $keyvalue2) {
            list($key,$value) = split("=", $keyvalue2);
            if ($key == "co") {
                $record->country_code = $value;
            }
            if ($key == "ci") {
                $record->city = $value;
            }
            if ($key == "re") {
                $record->region = $value;
            }
            if ($key == "ac") {
                $record->areacode = $value;
            }
            if ($key == "dm" || $key == "me") {
                $record->dmacode   = $value;
                $record->metrocode = $value;
            }
            if ($key == "is") {
                $record->isp = $value;
            }
            if ($key == "or") {
                $record->org = $value;
            }
            if ($key == "zi") {
                $record->postal_code = $value;
            }
            if ($key == "la") {
                $record->latitude = $value;
            }
            if ($key == "lo") {
                $record->longitude = $value;
            }
        }
          $number = Core::$GEOIP_COUNTRY_CODE_TO_NUMBER[$record->country_code];
          $record->country_code3 = Core::$GEOIP_COUNTRY_CODES3[$number];
          $record->country_name = Core::$GEOIP_COUNTRY_NAMES[$number];

          return $record;
    }

    static function _get_record($gi, $ipnum, Core $geoip)
    {
          $seek_country = $geoip->_geoip_seek_country($gi, $ipnum);
        if ($seek_country == $gi->databaseSegments) {
            return null;
        }

          // workaround php's broken substr, strpos, etc handling with
          // mbstring.func_overload and mbstring.internal_encoding
          $enc = mb_internal_encoding();
          mb_internal_encoding('ISO-8859-1');

          $record_pointer = $seek_country + (2 * $gi->record_length - 1) * $gi->databaseSegments;

        if ($gi->flags & Core::GEOIP_MEMORY_CACHE) {
            $record_buf = substr($gi->memory_buffer, $record_pointer, self::FULL_RECORD_LENGTH);
        } elseif ($gi->flags & Core::GEOIP_SHARED_MEMORY) {
            $record_buf = shmop_read($gi->shmid, $record_pointer, self::FULL_RECORD_LENGTH);
        } else {
            fseek($gi->filehandle, $record_pointer, SEEK_SET);
            $record_buf = fread($gi->filehandle, self::FULL_RECORD_LENGTH);
        }
          $record = new Record;
          $record_buf_pos = 0;
          $char = ord(substr($record_buf, $record_buf_pos, 1));
            $record->country_code = $gi->GEOIP_COUNTRY_CODES[$char];
            $record->country_code3 = $gi->GEOIP_COUNTRY_CODES3[$char];
            $record->country_name = $gi->GEOIP_COUNTRY_NAMES[$char];
          $record->continent_code = $gi->GEOIP_CONTINENT_CODES[$char];
          $record_buf_pos++;
          $str_length = 0;
            // Get region
          $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        while ($char != 0) {
            $str_length++;
            $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        }
        if ($str_length > 0) {
            $record->region = substr($record_buf, $record_buf_pos, $str_length);
        }
          $record_buf_pos += $str_length + 1;
          $str_length = 0;
            // Get city
          $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        while ($char != 0) {
            $str_length++;
            $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        }
        if ($str_length > 0) {
            $record->city = substr($record_buf, $record_buf_pos, $str_length);
        }
          $record_buf_pos += $str_length + 1;
          $str_length = 0;
            // Get postal code
          $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        while ($char != 0) {
            $str_length++;
            $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        }
        if ($str_length > 0) {
            $record->postal_code = substr($record_buf, $record_buf_pos, $str_length);
        }
          $record_buf_pos += $str_length + 1;
          $str_length = 0;
            // Get latitude and longitude
          $latitude = 0;
          $longitude = 0;
        for ($j = 0; $j < 3; ++$j) {
            $char = ord(substr($record_buf, $record_buf_pos++, 1));
            $latitude += ($char << ($j * 8));
        }
          $record->latitude = ($latitude/10000) - 180;
        for ($j = 0; $j < 3; ++$j) {
            $char = ord(substr($record_buf, $record_buf_pos++, 1));
            $longitude += ($char << ($j * 8));
        }
          $record->longitude = ($longitude/10000) - 180;
        if (Core::GEOIP_CITY_EDITION_REV1 == $gi->databaseType) {
            $metroarea_combo = 0;
            if ($record->country_code == "US") {
                for ($j = 0; $j < 3; ++$j) {
                    $char = ord(substr($record_buf, $record_buf_pos++, 1));
                    $metroarea_combo += ($char << ($j * 8));
                }
                $record->metro_code = $record->dma_code = floor($metroarea_combo/1000);
                $record->area_code = $metroarea_combo%1000;
            }
        }
          mb_internal_encoding($enc);
          return $record;
    }

    static function GeoIP_record_by_addr($gi, $addr, Core $geoip)
    {
        if ($addr == null) {
             return 0;
        }
          $ipnum = ip2long($addr);
          return self::_get_record($gi, $ipnum, $geoip);
    }
}
