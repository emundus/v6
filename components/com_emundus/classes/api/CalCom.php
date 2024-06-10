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
        $this->platform_oauth_client_id = "7774eb2e5e7d7b4387c2c3ed31c881512d07d90bd7fcee274ec6a4132f0c0e9c";
        $this->platform_oauth_client_secret = "e273621869bd693cfa186b790a46ad84880776dea895b905b299917642b97db6";
        $this->api_key = "cal_edcfa25c9fae2e8d476cb6c02c4dc265";

    }

    /**
     * @description Set the URL to Cal.com API v2
     */
    public function setBaseUrl(): void
    {
        $this->baseUrl = "http://192.168.1.72:3004";
    }

    /**
     * @description Set the URL to Cal.com API v1
     */
    public function setBaseUrlFirstVersionApi()
    {
        $this->baseUrl = "http://192.168.1.72:3003";
    }

    /**
     * @description Set headers for users' requests
     *
     * @value Content-Type : Indicates the type of content received (json here)
     * @value x-cal-secret-key : PlatformOAuthClient's secret
     */
    public function setHeadersUser(): void
    {
        $this->headers = array(
            'Content-Type' => "application/json",
            'x-cal-secret-key' => $this->platform_oauth_client_secret
        );
    }

    /**
     * @description Post request to create a Cal.com user
     *
     * @param $name string User's name that will be created
     * @param $time_format int Hours' format (12 or 24)
     * @param $week_start string First day of the week
     * @param $time_zone string User's timezone
     *
     */
    public function postUser($name, $time_format = 12, $week_start = "Monday", $time_zone = "Europe/Paris")
    {
        $this->setHeadersUser();

        $mail = $name;
        $mail = str_replace(' ', '_', $mail);
        $mail = htmlentities($mail, ENT_COMPAT, "UTF-8");
        $mail = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/', '$1',$mail);
        $mail= html_entity_decode($mail);
        $mail = preg_replace('/[^\x20-\x7E]/','', $mail);
        $mail = preg_replace('/[^a-zA-Z0-9_]/', '', $mail);
        $mail = strtolower($mail);

        $query_body = json_encode([
            "email" => $mail . "@emundus.com",
            "timeFormat" => $time_format,
            "weekStart" => $week_start,
            "timeZone" => $time_zone,
            "name" => $name
        ]);

        return $this->post("v2/oauth-clients/" . $this->platform_oauth_client_id . "/users", $query_body);
    }

    /**
     * @description Patch request to modify a Cal.com user
     *
     * @param $name string New user's name that will be modified
     * @param $user_id int User's ID to be modified
     * @param $schedule_id int Schedule ID related to the user
     * @param $time_format int Hours' format (12 or 24)
     * @param $week_start string First day of the week for user's calendar
     * @param $time_zone string User's timezone
     *
     */
    public function patchUser($name, $user_id, $schedule_id, $time_format = 12, $week_start = "Monday", $time_zone = "Europe/Paris")
    {
        $this->setHeadersUser();

        $mail = $name;
        $mail = str_replace(' ', '_', $mail);
        $mail = htmlentities($mail, ENT_COMPAT, "UTF-8");
        $mail = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/', '$1',$mail);
        $mail= html_entity_decode($mail);
        $mail = preg_replace('/[^\x20-\x7E]/','', $mail);
        $mail = preg_replace('/[^a-zA-Z0-9_]/', '', $mail);
        $mail = strtolower($mail);

        $query_body = json_encode([
            "email" => $mail . "@emundus.com",
            "timeFormat" => $time_format,
            "weekStart" => $week_start,
            "timeZone" => $time_zone,
            "name" => $name,
            "defaultScheduleId" => (int)$schedule_id
        ]);

        return $this->patch("v2/oauth-clients/" . $this->platform_oauth_client_id . "/users/" . $user_id, $query_body);
    }


    /**
     * @description Set headers for schedules' requests
     *
     * @param $access_token string : User's access token associated to the schedule
     *
     * @value accept : Indicates the type of content expected (json here)
     * @value Content-Type : Indicates the type of content received (json here)
     * @value Authorization : Authorize the request thanks to user's access token given
     * @value x-cal-secret-key : PlatformOAuthClient's secret
     */
    public function setHeadersSchedule($access_token): void
    {
        $this->headers = array(
            'accept' => "application/json",
            'Content-Type' => "application/json",
            'Authorization' => "Bearer " . $access_token,
            'x-cal-secret-key' => $this->platform_oauth_client_secret
        );

    }

    /**
     * @description Post request to create a Cal.com schedule
     *
     * @param $access_token string : User's access token associated to the schedule
     * @param $start_date string Schedule's start date
     * @param $end_date string Schedule's end date
     * @param $name string Schedule's name that will be created
     * @param $time_zone string Schedule's timezone
     *
     */
    public function postSchedule($access_token, $start_date, $end_date, $name="Default Schedule Name", $time_zone="Europe/Paris")
    {
        $this->setHeadersSchedule($access_token);

        $query_body = json_encode([
            "name" => $name,
            "timeZone" => $time_zone,
            "availabilities" => [
                [
                    "days" => [],
                    "startTime" => date('Y-m-d\TH:i', strtotime($start_date)) . ':00.000Z',
                    "endTime" => date('Y-m-d\TH:i', strtotime($end_date)) . ':00.000Z',
                ]
            ],
            "isDefault" => true
        ]);

        return $this->post("v2/schedules", $query_body);
    }

    /**
     * @description Patch request to modify a Cal.com schedule
     *
     * @param $access_token string : User's access token associated to the schedule
     * @param $schedule_id int Schedule's ID to be modified
     * @param $start_date string Schedule's start date
     * @param $end_date string Schedule's end date
     * @param $name string New schedule's name that will be modified
     * @param $time_zone string Schedule's timezone
     *
     */
    public function patchSchedule($access_token, $schedule_id, $start_date, $end_date, $name="Default Schedule Name", $time_zone="Europe/Paris")
    {
        $this->setHeadersSchedule($access_token);

        $end_date_iso = date('Y-m-d\TH:i', strtotime($end_date)) . ':00.000Z';

        $query_body =[
            "timeZone" => $time_zone,
            "name" => $name,
            "isDefault" => true,
            "dateOverrides" => [

            ]
        ];

        $current_date = strtotime($start_date);
        while ($current_date <= strtotime($end_date)) {
            $current_iso_date = date('Y-m-d', $current_date) . "T" . date('H:i', strtotime($start_date)). ":00.000Z";
            $query_body['dateOverrides'][] = [
                "start" => $current_iso_date,
                "end" => $end_date_iso
            ];
            $current_date = strtotime('+1 day', $current_date);
        }
        $query_body = json_encode($query_body);

        return $this->patch("v2/schedules/" . $schedule_id, $query_body);
    }

    /**
     * @description Delete request to remove a Cal.com schedule
     *
     * @param $access_token string : User's access token associated to the schedule
     * @param $schedule_id int Schedule's ID to be modified
     *
     */
    public function deleteSchedule($access_token, $schedule_id)
    {
        $this->setHeadersSchedule($access_token);

        return $this->delete("v2/schedules/" . $schedule_id);
    }

    /**
     * @description Post request to create a Cal.com event type
     *
     * @param $length string Duration of one event type
     * @param $title string Event type's name
     * @param $user_id int User's ID of event type's owner
     *
     */
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
            "title" => $title,
            "userId" => (int)$user_id

        ]);

        return $this->post("event-types?apiKey=" . $this->api_key, $query_body);
    }

    /**
     * @description Delete request to remove a Cal.com event type
     *
     * @param $event_type_id int : Event type's ID to delete
     *
     */
    public function deleteEventType($event_type_id)
    {
        $this->setBaseUrlFirstVersionApi();

        return $this->delete("event-types/" . $event_type_id . "?apiKey=" . $this->api_key);
    }

    /**
     * @description Force refreshing user's access token
     *
     * @param $user_id int : User's ID to refresh access token
     *
     */
    public function refreshingAccessToken($user_id)
    {
        $this->setHeadersUser();

        return $this->post("/v2/oauth-clients/" . $this->platform_oauth_client_id . "/users/" . $user_id . "/force-refresh");
    }

}