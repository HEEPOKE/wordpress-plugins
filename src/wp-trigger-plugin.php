<?php

/*
Plugin Name: WP Gitlab Trigger
Description: A plugin to Trigger your GitLab pipeline
Version: 1.0
Author: HEEPOKE
*/

namespace App\WpGitlabTrigger;

class WpTriggerPlugin
{
    private static $instance = null;
    private $tableName;
    private $wpdb;

    private function __construct($wpdb)
    {
        if ($wpdb) {
            $this->tableName = $wpdb->prefix . 'trigger_input';
            $this->wpdb = $wpdb;
            add_action('admin_menu', array($this, 'addAdminMenu'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'settingsLink'));
            add_action('admin_init', array($this, 'registerSettings'));
            add_action('edit_post', array($this, 'triggerGitlabApi'));
            add_action('wp_ajax_saveSettings', array($this, 'saveSettings'));
            add_action('wp_ajax_gitlabTriggerSubmit', array($this, 'gitlabTriggerSubmit'));
        }
    }

    public static function create($wpdb)
    {
        if (null === self::$instance) {
            self::$instance = new self($wpdb);
        }
        return self::$instance;
    }

    public function addAdminMenu()
    {
        global $wpdb;

        if ($wpdb) {
            add_options_page(
                'GitLab Trigger',
                'GitLab Trigger',
                'manage_options',
                'gitlab-trigger-settings',
                array($this, 'settingsPage')
            );
        }
    }

    public function registerSettings()
    {
        $fields = array(
            'gitlab_token' => 'GitLab Token',
            'gitlab_brand_tag' => 'Brand or Tag',
            'gitlab_project_id' => 'Project ID',
        );

        global $wpdb;

        if ($wpdb) {
            add_settings_section(
                'gitlab_trigger_section',
                'GitLab Trigger Pipeline',
                array($this, 'sectionCallback'),
                'gitlab-trigger-settings'
            );

            if (is_array($fields)) {
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
        }
    }

    public function settingsPage()
    {
        ?>
        <div class="wrap">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
                integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9"
                crossorigin="anonymous">
            <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.min.css" rel="stylesheet">
            <form method="post" action="">
                <?php if ($this->wpdb) {
                    settings_fields('gitlab_trigger_settings_group');
                    do_settings_sections('gitlab-trigger-settings');
                } ?>
                <div class="button-group" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" id="save-data" name="save-data">Save</button>
                    <button id="testButton" type="button" class="btn btn-secondary">Test Pipeline</button>
                    <button id="loadingButton" class="btn btn-primary d-none" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status">Loading...</span>
                    </button>
                </div>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
            </script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
            </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"
            integrity="sha384-Rx+T1VzGupg4BHQYs2gCW9It+akI2MM/mndMCy36UVfodzcJcF0GGLxZIzObiEfa" crossorigin="anonymous">
            </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.all.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const projectInput = document.querySelector('input[name="gitlab_project_id"]');
                const saveButton = document.getElementById('save-data');
                const testPipelineButton = document.getElementById('testButton');
                const loadingButton = document.getElementById('loadingButton');

                function handleResponse() {
                    loadingButton.classList.add('d-none');
                    testPipelineButton.classList.remove('d-none');
                }

                projectInput.addEventListener('input', () => {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });

                testPipelineButton.addEventListener('click', () => {
                    testPipelineButton.classList.add('d-none');
                    loadingButton.classList.remove('d-none');

                    jQuery.ajax({
                        type: "post",
                        url: `${window.location.origin}/wp-admin/admin-ajax.php`,
                        data: {
                            action: 'gitlabTriggerSubmit',
                            gitlab_token: document.getElementById("gitlab_token").value,
                            gitlab_brand_tag: document.getElementById("gitlab_brand_tag").value,
                            gitlab_project_id: document.getElementById("gitlab_project_id").value,
                        },
                        success: function (data) {
                            if (data.success) {
                                setTimeout(() => {
                                    handleResponse()
                                    showSwalNotification("success", "", true);
                                }, 5000);
                            } else {
                                setTimeout(() => {
                                    const errorMessage = typeof data.error === 'object' ?
                                        JSON.stringify(data.error) : data.error;
                                    handleResponse()
                                    showSwalNotification("error", errorMessage, true);
                                }, 5000);
                            }
                        },
                        error: function (error) {
                            setTimeout(() => {
                                handleResponse()
                                showSwalNotification("error", error, true);
                            }, 5000);
                        }
                    });
                });

                saveButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    saveSettingsViaAjax();
                });

                function saveSettingsViaAjax() {
                    jQuery.ajax({
                        type: "post",
                        url: `${window.location.origin}/wp-admin/admin-ajax.php`,
                        data: {
                            action: 'saveSettings',
                            gitlab_token: document.getElementById("gitlab_token").value,
                            gitlab_brand_tag: document.getElementById("gitlab_brand_tag").value,
                            gitlab_project_id: document.getElementById("gitlab_project_id").value,
                        },
                        success: function (data) {
                            if (data.success) {
                                showSwalNotification("success", "", false);
                            } else {
                                showSwalNotification("error", data.error, false);
                            }
                        },
                        error: function (error) {
                            showSwalNotification("error", error, false);
                        }
                    });
                }

                function showSwalNotification(type, message, reload) {
                    if (type === "success") {
                        Swal.fire({
                            title: 'Success!',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 2000
                        }).then((res) => {
                            if (res.isConfirmed && !realod) {
                                window.location.reload();
                            }
                        });
                    } else if (type === "error") {
                        Swal.fire({
                            title: 'Error!',
                            text: `Save Error: ${message}`,
                            icon: 'error',
                            showConfirmButton: false,
                            showDenyButton: false,
                            timer: 2000
                        }).then((res) => {
                            if (res.isDenied && !realod) {
                                window.location.reload();
                            }
                        });
                    }
                }
            });
        </script>
        <?php
    }

    public function sectionCallback()
    {
        echo '<p>Welcome to the GitLab Trigger settings section. Configure the plugin settings below:</p>';
        echo '<ul style="padding-left: 0">';
        echo '<li><strong>GitLab Token:</strong> Enter your GitLab access token for authentication.</li>';
        echo '<li><strong>Brand or Tag:</strong> Specify the brand or tag triggering the pipeline.</li>';
        echo '<li><strong>Project ID:</strong> Provide the project ID for pipeline triggering.</li>';
        echo '</ul>';
    }

    public function fieldCallback($args)
    {
        global $wpdb;

        $field_name = $args['field'];

        if ($wpdb) {
            $result = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT {$field_name} FROM {$this->tableName} ORDER BY id DESC LIMIT 1"
                )
            );
            echo "<input type='text' id='{$field_name}'  name='{$field_name}' value='{$result}' />";
        }
    }

    public function saveSettings($test_post)
    {

        $fields = array('gitlab_token', 'gitlab_brand_tag', 'gitlab_project_id');
        $data = array();

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            }
        }
        $response = array('success' => false);

        if (!empty($data)) {
            $result = $this->wpdb->insert($this->tableName, $data);

            if ($result !== false) {
                $response['success'] = true;
            }
        }

        if (!$test_post || empty($test_post)) {
            wp_send_json($response);
        }
        if ($this->wpdb) {
            wp_die();
        }
    }

    public function triggerGitlabApi()
    {
        global $wpdb;

        $response = array();

        if ($wpdb) {
            $table_name = $wpdb->prefix . 'trigger_input';
            $data = $wpdb->get_row(
                "SELECT gitlab_token, gitlab_brand_tag, gitlab_project_id FROM $table_name ORDER BY id DESC LIMIT 1"
            );

            if ($data) {
                $privateToken = $data->gitlab_token;
                $brand_tag = $data->gitlab_brand_tag;
                $project_id = $data->gitlab_project_id;

                $api_url = "https://gitlab.com/api/v4/projects/{$project_id}/pipeline?ref={$brand_tag}";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("PRIVATE-TOKEN: $privateToken"));

                $response['response'] = curl_exec($ch);
                $response['error'] = curl_error($ch);

                curl_close($ch);
            }
        }
        return json_encode($response);
    }

    public function gitlabTriggerSubmit()
    {
        if ($this->wpdb) {
            if (
                $_SERVER['REQUEST_METHOD'] === 'POST' &&
                isset($_POST['gitlab_token']) &&
                isset($_POST['gitlab_brand_tag']) &&
                isset($_POST['gitlab_project_id'])
            ) {
                $privateToken = $_POST['gitlab_token'];
                $tag = $_POST['gitlab_brand_tag'];
                $project_id = $_POST['gitlab_project_id'];

                if (empty($privateToken) || empty($tag) || empty($project_id)) {
                    $error_response = array('success' => false, 'error' => 'Data Not Correct');
                    wp_send_json($error_response);
                    return;
                }

                $host = "gitlab.com";
                $api_url = "https://{$host}/api/v4/projects/{$project_id}/pipeline?ref={$tag}";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("PRIVATE-TOKEN: $privateToken"));
                $response = curl_exec($ch);
                curl_error($ch);
                curl_close($ch);

                $responseData = json_decode($response, true);

                if (isset($responseData['message'])) {
                    $error_response = array('success' => false, 'error' => $responseData['message']);
                    wp_send_json($error_response);
                } else {
                    $success_response = array('success' => true);
                    wp_send_json($success_response);
                }
            } else {
                $error_response = array('success' => false, 'error' => 'Invalid request format');
                wp_send_json($error_response);
            }
        }
    }

    public static function settingsLink($links)
    {
        global $wpdb;

        if ($wpdb) {
            $settings_link = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=gitlab-trigger-settings'));
            return array_merge([$settings_link], $links);
        }
    }

    public static function wpGitlabTriggerActivation()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'trigger_input';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $tableName (
        id int(11) NOT NULL AUTO_INCREMENT,
        gitlab_token varchar(255) NOT NULL,
        gitlab_brand_tag varchar(255) NOT NULL,
        gitlab_project_id varchar(255) NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

global $wpdb;
$plugin_instance = WpTriggerPlugin::create($wpdb);

if ($wpdb) {
    register_activation_hook(__FILE__, array($plugin_instance, 'wpGitlabTriggerActivation'));
}
