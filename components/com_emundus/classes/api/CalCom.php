<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr
 * @copyright (C) 2024 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace classes\api;

defined('_JEXEC') or die('Restricted access');
class CalCom extends Api
{

    public function __construct()
    {
        parent::__construct();

        $this->setBaseUrl();
        $this->setClient();
        // $this->setAuth();
        $this->platform_oauth_clientId = "7774eb2e5e7d7b4387c2c3ed31c881512d07d90bd7fcee274ec6a4132f0c0e9c";
        $this->platform_oauth_clientSecret = "e273621869bd693cfa186b790a46ad84880776dea895b905b299917642b97db6";
        $this->api_key = "cal_edcfa25c9fae2e8d476cb6c02c4dc265";

    }

    public function setBaseUrl(): void
    {
        $this->baseUrl = "http://192.168.1.72:3004";
    }

    public function setBaseUrlFirstVersionApi()
    {
        $this->baseUrl = "http://192.168.1.72:3003";
    }

    public function setHeadersPostUser(): void
    {
        $this->headers = array(
            'Content-Type' => "application/json",
            'x-cal-secret-key' => $this->platform_oauth_clientSecret
        );
    }

    public function postUser($name, $time_format = 12, $week_start = "Monday", $time_zone = "Europe/Paris")
    {
        $this->setHeadersPostUser();

        $query_body = json_encode([
            "email" => $name . "@emundus.com",
            "timeFormat" => $time_format,
            "weekStart" => $week_start,
            "timeZone" => $time_zone,
            "name" => $name
        ]);

        return $this->post("v2/oauth-clients/" . $this->platform_oauth_clientId . "/users", $query_body);
    }

    public function patchUser($name, $user_id, $schedule_id, $time_format = 12, $week_start = "Monday", $time_zone = "Europe/Paris")
    {
        $this->setHeadersPostUser();

        $query_body = json_encode([
            "email" => $name . "@emundus.com",
            "timeFormat" => $time_format,
            "weekStart" => $week_start,
            "timeZone" => $time_zone,
            "name" => $name,
            "defaultScheduleId" => (int)$schedule_id
        ]);

        return $this->patch("v2/oauth-clients/" . $this->platform_oauth_clientId . "/users/" . $user_id, $query_body);
    }


    public function setHeadersPostSchedule($access_token): void
    {
        $this->headers = array(
            'accept' => "application/json",
            'Content-Type' => "application/json",
            'Authorization' => "Bearer " . $access_token,
            'x-cal-secret-key' => $this->platform_oauth_clientSecret
        );

    }

    public function postSchedule($access_token, $name="Default Schedule Name", $time_zone="Europe/Paris")
    {
        $this->setHeadersPostSchedule($access_token);

        $query_body = json_encode([
            "name" => $name,
            "timeZone" => $time_zone,
            "availabilities" => [
                [
                    "days" => [],
                    "startTime" => "1900-06-06T00:01:00.000Z",
                    "endTime" => "1900-07-06T23:59:00.000Z",
                ]
            ],
            "isDefault" => true
        ]);

        return $this->post("v2/schedules", $query_body);
    }

    public function patchSchedule($access_token, $schedule_id, $start_date, $end_date, $name="Default Schedule Name", $time_zone="Europe/Paris")
    {
        $this->setHeadersPostSchedule($access_token);

        $end_date_iso = date('Y-m-d\TH:i', strtotime($end_date)) . ':00.000Z';

        $query_body = json_encode([
            "timeZone" => $time_zone,
            "name" => $name,
            "isDefault" => true,
            "dateOverrides" => [

            ]
        ]);

        $query_body_array = json_decode($query_body, true);

        $current_date = strtotime($start_date);
        while ($current_date <= strtotime($end_date)) {
            $current_iso_date = date('Y-m-d', $current_date) . "T" . date('H:i', strtotime($start_date)). ":00.000Z";
            $query_body_array['dateOverrides'][] = [
                "start" => $current_iso_date,
                "end" => $end_date_iso
            ];
            $current_date = strtotime('+1 day', $current_date);
        }

        $query_body = json_encode($query_body_array);

        return $this->patch("v2/schedules/" . $schedule_id, $query_body);
    }

    public function deleteSchedule($access_token, $schedule_id)
    {
        $this->setHeadersPostSchedule($access_token);

        return $this->delete("v2/schedules/" . $schedule_id);
    }

    public function postEventType($length, $title, $user_id)
    {
        $this->setBaseUrlFirstVersionApi();

        $slug = $title;
        $slug = str_replace(' ', '_', $slug);
        $slug = htmlentities($slug, ENT_COMPAT, "UTF-8");
        $slug = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/', '$1',$slug);
        $slug= html_entity_decode($slug);
        $slug = preg_replace('/[^\x20-\x7E]/','', $slug);
        $slug = preg_replace('/[^a-zA-Z0-9_]/', '', $slug);
        $slug = strtolower($slug);

        $query_body = json_encode([
            "length" => (int)$length,
            "slug" => $slug . "_event",
            "title" => $title . " Event",
            "userId" => (int)$user_id

        ]);

        return $this->post("event-types?apiKey=" . $this->api_key, $query_body);
    }

    public function deleteEventType($event_type_id)
    {
        $this->setBaseUrlFirstVersionApi();

        return $this->delete("event-types/" . $event_type_id . "?apiKey=" . $this->api_key);
    }

    public function refreshingAccessToken($user_id)
    {
        $this->setHeadersPostUser();

        return $this->post("/v2/oauth-clients/" . $this->platform_oauth_clientId . "/users/" . $user_id . "/force-refresh");
    }

}