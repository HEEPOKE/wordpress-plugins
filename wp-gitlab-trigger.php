<?php

/*
Plugin Name: WP Gitlab Trigger
Description: A plugin to Trigger your GitLab pipeline
Version: 1.0
Author: HEEPOKE
*/

class WpGitlabTrigger
{
    private static $instance = null;
    private $tableName;

    private function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'trigger_input';

        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_init', array($this, 'gitlabTriggerSubmit'));
        add_action('edit_post', array($this, 'triggerGitlabApi'));
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addAdminMenu()
    {
        add_options_page(
            'GitLab Trigger',
            'GitLab Trigger',
            'manage_options',
            'gitlab-trigger-settings',
            array($this, 'settingsPage')
        );
    }

    public function registerSettings()
    {
        $fields = array(
            'gitlab_token' => 'GitLab Token',
            'gitlab_brand_tag' => 'Brand or Tag',
            'gitlab_project_id' => 'Project ID',
        );

        add_settings_section(
            'gitlab_trigger_section',
            'GitLab Trigger Pipeline',
            array($this, 'sectionCallback'),
            'gitlab-trigger-settings'
        );

        foreach ($fields as $field => $label) {
            add_settings_field(
                $field,
                $label,
                array($this, 'fieldCallback'),
                'gitlab-trigger-settings',
                'gitlab_trigger_section',
                array('field' => $field)
            );
            register_setting('gitlab_trigger_settings_group', $field);
        }
    }

    public function settingsPage()
    {
        ?>
        <div class="wrap">
            <form method="post" action="">
                <?php
                settings_fields('gitlab_trigger_settings_group');
                do_settings_sections('gitlab-trigger-settings');
                ?>

                <div class="button-group" style="display: flex; gap: 10px;">
                    <?php submit_button('Save', 'primary', 'save-data'); ?>
                    <?php submit_button('Test Pipeline', 'primary', 'submit'); ?>
                </div>
                <?php $this->saveSettings("test-pipeline"); ?>
            </form>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const projectInput = document.querySelector('input[name="gitlab_project_id"]');
                const saveButton = document.querySelector('input[name="save-data"]');

                projectInput.addEventListener('input', () => {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });

                saveButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    saveSettingsViaAjax();
                });

                function handleSuccess() {
                    alert('saved successfully');
                }

                function handleFailure() {
                    alert('Error saving');
                }

                function saveSettingsViaAjax() {
                    const formData = new FormData(document.querySelector('form'));
                    formData.append('action', 'saveSettings');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }
            });
        </script>
        <?php
    }

    public function sectionCallback()
    {
        echo '<p>Welcome to the GitLab Trigger settings section. Configure the plugin settings below:</p>';
        echo '<ul>';
        echo '<li><strong>GitLab Token:</strong> Enter your GitLab access token for authentication.</li>';
        echo '<li><strong>Brand or Tag:</strong> Specify the brand or tag triggering the pipeline.</li>';
        echo '<li><strong>Project ID:</strong> Provide the project ID for pipeline triggering.</li>';
        echo '</ul>';
    }

    public function fieldCallback($args)
    {
        global $wpdb;

        $field_name = $args['field'];

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT {$field_name} FROM {$this->tableName} ORDER BY id DESC LIMIT 1"
            )
        );

        echo "<input type='text' id='{$field_name}'  name='{$field_name}' value='{$result}' />";
    }

    public function saveSettings($check)
    {
        global $wpdb;

        $fields = array('gitlab_token', 'gitlab_brand_tag', 'gitlab_project_id');
        $data = array();

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = sanitize_text_field($_POST[$field]);
            }
        }

        if (!empty($data)) {
            $result = $wpdb->insert($this->tableName, $data);

            if ($result !== false && empty($check)) {
                echo '<script>alert("Saved successfully"); window.location.reload();</script>';
            } elseif (!empty($check)) {
                echo '<script>window.location.reload();</script>';
            } else {
                echo '<script>alert("Error saving");</script>';
            }
        }
        wp_die();
    }

    public function triggerGitlabApi()
    {
        $privateToken = get_option('gitlab_token');
        $brand_tag = get_option('gitlab_brand_tag');
        $project_id = get_option('gitlab_project_id');

        $api_url = "https://gitlab.com/api/v4/projects/{$project_id}/pipeline?ref={$brand_tag}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("PRIVATE-TOKEN: $privateToken"));

        $response = array(
            'response' => curl_exec($ch),
            'error' => curl_error($ch)
        );

        if ($response['error']) {
            echo '<script>alert("Error: ' . addslashes($response['error']) . '");</script>';
        }

        curl_close($ch);
    }

    public function gitlabTriggerSubmit()
    {
        if (isset($_POST['submit'])) {
            $this->triggerGitlabApi();
        }
    }
}

function wpGitlabTriggerActivation()
{
    global $wpdb;
    $tableName = $wpdb->prefix . 'trigger_input';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $tableName (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        gitlab_token varchar(255) NOT NULL,
        gitlab_brand_tag varchar(255) NOT NULL,
        gitlab_project_id varchar(255) NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

WpGitlabTrigger::getInstance();
register_activation_hook(__FILE__, 'wpGitlabTriggerActivation');
