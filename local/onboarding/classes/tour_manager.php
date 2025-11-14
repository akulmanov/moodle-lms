<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_onboarding;

use core_component;
use core_text;
use moodle_exception;
use tool_usertours\step;
use tool_usertours\target;
use tool_usertours\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper responsible for provisioning and maintaining the onboarding user tour.
 *
 * @package   local_onboarding
 * @copyright 2024
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class tour_manager {
    /** Default pathmask to attach the tour to. */
    private const PATH_MATCH = '/my/%';

    /** CSS selector targets used in the tour. */
    private const TARGET_DASHBOARD = '.block_myoverview';
    private const TARGET_COURSECATALOG = 'a[href*="/course/index.php"]';
    private const TARGET_CERTIFICATES = 'a[href*="/badges/mybadges.php"]';
    private const TARGET_NOTIFICATIONS = '#nav-notification-popover-container';

    /**
     * Ensure that the onboarding tour exists and return its ID.
     *
     * @return int
     * @throws moodle_exception If the tool_usertours component is disabled.
     */
    public static function ensure_tour_exists(): int {
        global $DB;

        if (!core_component::is_component_present('tool_usertours')) {
            throw new moodle_exception('missingcomponent', 'error', '', 'tool_usertours');
        }

        $configuredid = (int)get_config('local_onboarding', 'tourid');
        if ($configuredid > 0 && $DB->record_exists('tool_usertours_tours', ['id' => $configuredid])) {
            return $configuredid;
        }

        $existing = $DB->get_record('tool_usertours_tours', ['name' => self::get_string('tourname')]);
        if ($existing) {
            set_config('tourid', $existing->id, 'local_onboarding');
            return (int)$existing->id;
        }

        $tour = self::create_tour();
        self::create_steps($tour);
        self::mark_existing_users_completed($tour->get_id());

        set_config('tourid', $tour->get_id(), 'local_onboarding');

        return $tour->get_id();
    }

    /**
     * Create the base tour entity.
     *
     * @return tour
     */
    private static function create_tour(): tour {
        $tour = new tour();
        $tour->set_name(self::get_string('tourname'))
            ->set_description(self::get_string('tourdescription'))
            ->set_pathmatch(self::PATH_MATCH)
            ->set_enabled(true)
            ->set_display_step_numbers(true)
            ->set_config('backdrop', true)
            ->set_config('orphan', false)
            ->set_config('reflex', false)
            ->set_showtourwhen(tour::SHOW_TOUR_UNTIL_COMPLETE)
            ->set_endtourlabel(self::get_string('tourendlabel'))
            ->persist(true);

        return $tour;
    }

    /**
     * Create all tour steps.
     *
     * @param tour $tour
     * @return void
     */
    private static function create_steps(tour $tour): void {
        self::create_step(
            $tour,
            self::get_string('step_dashboard_title'),
            self::get_string('step_dashboard_content'),
            self::TARGET_DASHBOARD
        );

        self::create_step(
            $tour,
            self::get_string('step_courses_title'),
            self::get_string('step_courses_content'),
            self::TARGET_COURSECATALOG
        );

        self::create_step(
            $tour,
            self::get_string('step_certificates_title'),
            self::get_string('step_certificates_content'),
            self::TARGET_CERTIFICATES
        );

        self::create_step(
            $tour,
            self::get_string('step_notifications_title'),
            self::get_string('step_notifications_content'),
            self::TARGET_NOTIFICATIONS
        );
    }

    /**
     * Create a single step record.
     *
     * @param tour $tour
     * @param string $title
     * @param string $content
     * @param string $selector
     * @return step
     */
    private static function create_step(tour $tour, string $title, string $content, string $selector): step {
        $step = new step();
        $step->set_tourid($tour->get_id())
            ->set_title($title)
            ->set_content($content, FORMAT_HTML)
            ->set_targettype(target::TARGET_SELECTOR)
            ->set_targetvalue($selector)
            ->set_config('backdrop', true)
            ->persist(true);

        return $step;
    }

    /**
     * Mark all existing users as having completed the tour to avoid re-triggering.
     *
     * New accounts will not have this flag and will pick up the tour automatically.
     *
     * @param int $tourid
     * @return void
     */
    private static function mark_existing_users_completed(int $tourid): void {
        global $DB;

        $prefname = tour::TOUR_LAST_COMPLETED_BY_USER . $tourid;
        $DB->delete_records('user_preferences', ['name' => $prefname]);

        $users = $DB->get_records_select('user', 'deleted = 0', null, '', 'id');
        if (empty($users)) {
            return;
        }

        $time = time();
        $batch = [];
        foreach ($users as $user) {
            if (isguestuser($user) || (int)$user->id <= 0) {
                continue;
            }

            $batch[] = [
                'userid' => $user->id,
                'name' => $prefname,
                'value' => $time,
            ];

            if (count($batch) >= 500) {
                $DB->insert_records('user_preferences', $batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $DB->insert_records('user_preferences', $batch);
        }
    }

    /**
     * Shortcut for retrieving component strings with core_text handling.
     *
     * @param string $identifier
     * @return string
     */
    private static function get_string(string $identifier): string {
        $text = get_string($identifier, 'local_onboarding');
        return core_text::entities_to_utf8($text);
    }
}

