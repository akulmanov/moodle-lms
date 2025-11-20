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

defined('MOODLE_INTERNAL') || die();

/**
 * Admin settings page with tabs support.
 *
 * @package    local_mlangdefaults
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_mlangdefaults_admin_settingspage_tabs extends admin_settingpage {

    /** @var array The tabs */
    protected $tabs = array();

    /**
     * Add a tab.
     *
     * @param admin_settingpage $tab A tab.
     */
    public function add_tab(admin_settingpage $tab) {
        foreach ($tab->settings as $setting) {
            $this->settings->{$setting->name} = $setting;
        }
        $this->tabs[] = $tab;
        return true;
    }

    /**
     * Get tabs.
     *
     * @return array
     */
    public function get_tabs() {
        return $this->tabs;
    }

    /**
     * Show we display Save button at the page bottom?
     * Override to ensure save button is shown when we have tabs with settings.
     *
     * @return bool
     */
    public function show_save() {
        // If we have tabs, check if any tab has saveable settings.
        if (!empty($this->tabs)) {
            foreach ($this->tabs as $tab) {
                foreach ($tab->settings as $setting) {
                    if (empty($setting->nosave)) {
                        return true;
                    }
                }
            }
        }
        // Fall back to parent implementation.
        return parent::show_save();
    }

    /**
     * Generate the HTML output with tabs.
     *
     * @return string
     */
    public function output_html() {
        global $OUTPUT, $PAGE;

        $activetab = optional_param('activetab', '', PARAM_TEXT);
        $tabs = array();
        $havesetactive = false;

        foreach ($this->get_tabs() as $tab) {
            $active = false;

            // Default to first tab if not told otherwise.
            if (empty($activetab) && !$havesetactive) {
                $active = true;
                $havesetactive = true;
            } else if ($activetab === $tab->name) {
                $active = true;
            }

            $tabs[] = array(
                'name' => $tab->name,
                'displayname' => $tab->visiblename,
                'html' => $tab->output_html(),
                'active' => $active,
            );
        }

        if (empty($tabs)) {
            return '';
        }

        $tabcontainerid = 'mlangdefaults-tabs-' . $this->name;

        // Generate tabs HTML with hash anchors for Bootstrap tabs.
        $tabshtml = '<div id="' . $tabcontainerid . '"><ul class="nav nav-tabs mb-3" role="tablist">';
        foreach ($tabs as $tab) {
            $activeclass = $tab['active'] ? 'active' : '';
            $url = new moodle_url($PAGE->url, ['activetab' => $tab['name']]);
            $tabshtml .= '<li class="nav-item">';
            $tabshtml .= '<a href="#' . $tab['name'] . '" class="nav-link ' . $activeclass . '" data-bs-toggle="tab" role="tab"';
            $tabshtml .= ' data-tab-url="' . htmlspecialchars($url->out(false)) . '"';
            if ($tab['active']) {
                $tabshtml .= ' aria-selected="true"';
            } else {
                $tabshtml .= ' aria-selected="false" tabindex="-1"';
            }
            $tabshtml .= '>' . htmlspecialchars($tab['displayname']) . '</a>';
            $tabshtml .= '</li>';
        }
        $tabshtml .= '</ul>';

        $contenthtml = '<div class="tab-content">';
        foreach ($tabs as $tab) {
            $activeclass = $tab['active'] ? 'active show' : '';
            $contenthtml .= '<div class="tab-pane fade ' . $activeclass . '" id="' . $tab['name'] . '" role="tabpanel">';
            $contenthtml .= $tab['html'];
            $contenthtml .= '</div>';
        }
        $contenthtml .= '</div></div>';

        // Note: The save button is automatically rendered by Moodle's admin settings template
        // after this HTML output. It will appear at the bottom of the form.

        // Add JavaScript to handle tab switching and URL updates.
        $PAGE->requires->js_init_code('
            (function() {
                require(["jquery"], function($) {
                    var activetab = "' . ($activetab ?: '') . '";
                    var container = $("#' . $tabcontainerid . '");
                    
                    // Manual tab switching function.
                    function switchTab(tabLink) {
                        var targetId = tabLink.attr("href").substring(1);
                        var tabPane = container.find("#" + targetId);
                        
                        // Hide all tabs and remove active class.
                        container.find(".nav-link").removeClass("active").attr("aria-selected", "false");
                        container.find(".tab-pane").removeClass("active show");
                        
                        // Show selected tab.
                        tabLink.addClass("active").attr("aria-selected", "true");
                        tabPane.addClass("active show");
                        
                        // Update URL.
                        var tabUrl = tabLink.attr("data-tab-url");
                        if (tabUrl && window.history && window.history.pushState) {
                            window.history.pushState(null, "", tabUrl);
                        }
                    }
                    
                    // Handle tab clicks.
                    container.find(\'a[data-bs-toggle="tab"]\').on("click", function(e) {
                        e.preventDefault();
                        switchTab($(this));
                    });
                    
                    // Show the active tab on page load if specified.
                    if (activetab) {
                        var tabLink = container.find(\'a[href="#\' + activetab + \'"]\');
                        if (tabLink.length) {
                            switchTab(tabLink);
                        }
                    }
                });
            })();
        ');

        return $tabshtml . $contenthtml;
    }
}

