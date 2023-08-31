<?php
/*
Plugin Name: WP Gitlab Trigger
Description: A plugin to Trigger your GitLab pipeline
Version: 1.0
Author: HEEPOKE
*/

function gitlabTriggerSettingsMenu()
{
    add_options_page(
        'GitLab Trigger',
        'GitLab Trigger',
        'manage_options',
        'gitlab-trigger-settings',
        'gitlabTriggerPage'
    );
}

function gitlabTriggerSubmit()
{
    if (isset($_POST['submit'])) {
        triggeGitlabApi();
    }
}

function gitlabSaveSubmit()
{
    if (isset($_POST['save_data'])) {
        saveSettings();
    }
}

function gitlabTokenCallback()
{
    $gitlab_token = get_option('gitlab_token');
    echo '<input type="text" name="gitlab_token" value="' . esc_attr($gitlab_token) . '" />';
}

function gitlabBrandTagCallback()
{
    $brand_tag = get_option('gitlab_brand_tag');
    echo '<input type="text" name="gitlab_brand_tag" value="' . esc_attr($brand_tag) . '" />';
}

function gitlabProjectIdCallback()
{
    $project_id = get_option('gitlab_project_id');
    echo '<input type="text" name="gitlab_project_id" value="' . esc_attr($project_id) . '" />';
}

function saveSettings()
{
    $gitlab_token = isset($_POST['gitlab_token'])
        ? sanitize_text_field($_POST['gitlab_token'])
        : get_option('gitlab_token');

    $brand_tag = isset($_POST['gitlab_brand_tag'])
        ? sanitize_text_field($_POST['gitlab_brand_tag'])
        : get_option('gitlab_brand_tag');

    $project_id = isset($_POST['gitlab_project_id'])
        ? sanitize_text_field($_POST['gitlab_project_id'])
        : get_option('gitlab_project_id');

    update_option('gitlab_token', $gitlab_token);
    update_option('gitlab_brand_tag', $brand_tag);
    update_option('gitlab_project_id', $project_id);
}

function triggeGitlabApi()
{
    $privateToken = get_option('gitlab_token');
    $brand_tag = get_option('gitlab_brand_tag');
    $project_id = get_option('gitlab_project_id');

    $api_url = "https://gitlab.com/api/v4/projects/{$project_id}/pipeline?ref={$brand_tag}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "PRIVATE-TOKEN: $privateToken",
    ]);

    $response = array(
        'response' => curl_exec($ch),
        'error' => curl_error($ch)
    );

    if ($response['error']) {
        echo '<script>alert("Error: ' . addslashes($response['error']) . '");</script>';
    }

    curl_close($ch);
}

function gitlabTriggerSectionCallback()
{
    echo '<p>Welcome to the GitLab Trigger settings section. Configure the plugin settings below:</p>';
    echo '<ul>';
    echo '<li><strong>GitLab Token:</strong> Enter your GitLab access token for authentication.</li>';
    echo '<li><strong>Brand or Tag:</strong> Specify the brand or tag triggering the pipeline.</li>';
    echo '<li><strong>Project ID:</strong> Provide the project ID for pipeline triggering.</li>';
    echo '</ul>';
}

function gitlabTriggerRegisterSettings()
{
    add_settings_section(
        'gitlab_trigger_section',
        'GitLab Trigger Pipeline',
        'gitlabTriggerSectionCallback',
        'gitlab-trigger-settings'
    );

    add_settings_field(
        'gitlab_token',
        'GitLab Token',
        'gitlabTokenCallback',
        'gitlab-trigger-settings',
        'gitlab_trigger_section'
    );

    add_settings_field(
        'gitlab_brand_tag',
        'Brand or Tag',
        'gitlabBrandTagCallback',
        'gitlab-trigger-settings',
        'gitlab_trigger_section'
    );

    add_settings_field(
        'gitlab_project_id',
        'Project ID',
        'gitlabProjectIdCallback',
        'gitlab-trigger-settings',
        'gitlab_trigger_section'
    );

    register_setting(
        'gitlab_trigger_settings_group',
        'gitlab_token'
    );

    register_setting(
        'gitlab_trigger_settings_group',
        'gitlab_brand_tag'
    );

    register_setting(
        'gitlab_trigger_settings_group',
        'gitlab_project_id'
    );
}

function gitlabTriggerPage()
{
?>
    <div class="wrap">
        <form method="post" action="">
            <?php
            settings_fields('gitlab_trigger_settings_group');
            do_settings_sections('gitlab-trigger-settings');
            gitlabSaveSubmit();
            ?>

            <div class="button-group" style="display: flex; gap: 10px;">
                <?php submit_button('Save', 'primary', 'save-data'); ?>
                <?php submit_button('Test Pipeline', 'primary', 'submit'); ?>
            </div>

            <?php saveSettings(); ?>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const projectInput = document.querySelector('input[name="gitlab_project_id"]');

            projectInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
<?php
}

add_action('admin_menu', 'gitlabTriggerSettingsMenu');
add_action('admin_init', 'gitlabSaveSubmit');
add_action('admin_init', 'gitlabTriggerSubmit');
add_action('admin_init', 'gitlabTriggerRegisterSettings');
add_action('save_post', 'triggeGitlabApi');
