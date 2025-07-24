<?php
/**
 * Unit tests for ConfigManager class
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi;

use advanced_testcase;
use mod_videoxapi\xapi\ConfigManager;

/**
 * Test ConfigManager functionality
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_manager_test extends advanced_testcase {

    /**
     * Set up test fixtures
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test LRS endpoint configuration
     */
    public function test_lrs_endpoint_config() {
        $endpoint = 'https://example.com/xapi';
        
        $this->assertTrue(ConfigManager::setLrsEndpoint($endpoint));
        $this->assertEquals($endpoint, ConfigManager::getLrsEndpoint());

        // Test invalid endpoint.
        $this->assertFalse(ConfigManager::setLrsEndpoint('http://insecure.com/xapi'));
        $this->assertFalse(ConfigManager::setLrsEndpoint('not-a-url'));
    }    /**
     * Test credential encryption and decryption
     */
    public function test_credential_encryption() {
        $username = 'testuser';
        $password = 'testpass123';

        // Set credentials.
        $this->assertTrue(ConfigManager::setLrsUsername($username));
        $this->assertTrue(ConfigManager::setLrsPassword($password));

        // Retrieve credentials.
        $this->assertEquals($username, ConfigManager::getLrsUsername());
        $this->assertEquals($password, ConfigManager::getLrsPassword());

        // Verify that stored values are encrypted.
        $storedUsername = get_config('mod_videoxapi', 'lrs_username');
        $storedPassword = get_config('mod_videoxapi', 'lrs_password');
        
        $this->assertNotEquals($username, $storedUsername);
        $this->assertNotEquals($password, $storedPassword);
    }

    /**
     * Test authentication method configuration
     */
    public function test_auth_method_config() {
        // Test valid methods.
        $this->assertTrue(ConfigManager::setLrsAuthMethod('basic'));
        $this->assertEquals('basic', ConfigManager::getLrsAuthMethod());

        $this->assertTrue(ConfigManager::setLrsAuthMethod('oauth'));
        $this->assertEquals('oauth', ConfigManager::getLrsAuthMethod());

        // Test invalid method.
        $this->assertFalse(ConfigManager::setLrsAuthMethod('invalid'));
    }

    /**
     * Test xAPI enabled configuration
     */
    public function test_xapi_enabled_config() {
        // Default should be false.
        $this->assertFalse(ConfigManager::isXapiEnabled());

        // Test enabling.
        $this->assertTrue(ConfigManager::setXapiEnabled(true));
        $this->assertTrue(ConfigManager::isXapiEnabled());

        // Test disabling.
        $this->assertTrue(ConfigManager::setXapiEnabled(false));
        $this->assertFalse(ConfigManager::isXapiEnabled());
    }

    /**
     * Test configuration validation
     */
    public function test_config_validation() {
        // Test valid configuration.
        $validConfig = [
            'endpoint' => 'https://example.com/xapi',
            'username' => 'testuser',
            'password' => 'testpass',
            'auth_method' => 'basic'
        ];

        $result = ConfigManager::validateLrsConfig($validConfig);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        // Test invalid configuration.
        $invalidConfig = [
            'endpoint' => 'http://insecure.com', // Not HTTPS.
            'username' => '', // Empty.
            'password' => '', // Empty.
            'auth_method' => 'invalid' // Invalid method.
        ];

        $result = ConfigManager::validateLrsConfig($invalidConfig);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('endpoint', $result['errors']);
        $this->assertArrayHasKey('username', $result['errors']);
        $this->assertArrayHasKey('password', $result['errors']);
        $this->assertArrayHasKey('auth_method', $result['errors']);
    }    /**
     * Test getting complete LRS configuration
     */
    public function test_get_lrs_config() {
        // Set up configuration.
        ConfigManager::setLrsEndpoint('https://example.com/xapi');
        ConfigManager::setLrsUsername('testuser');
        ConfigManager::setLrsPassword('testpass');
        ConfigManager::setLrsAuthMethod('oauth');
        ConfigManager::setXapiEnabled(true);
        ConfigManager::setQueueEnabled(false);

        $config = ConfigManager::getLrsConfig();

        $this->assertEquals('https://example.com/xapi', $config['endpoint']);
        $this->assertEquals('testuser', $config['username']);
        $this->assertEquals('testpass', $config['password']);
        $this->assertEquals('oauth', $config['auth_method']);
        $this->assertTrue($config['xapi_enabled']);
        $this->assertFalse($config['queue_enabled']);
    }

    /**
     * Test configuration reset
     */
    public function test_config_reset() {
        // Set some configuration.
        ConfigManager::setLrsEndpoint('https://example.com/xapi');
        ConfigManager::setLrsUsername('testuser');
        ConfigManager::setXapiEnabled(true);

        // Reset configuration.
        $this->assertTrue(ConfigManager::resetConfig());

        // Verify defaults.
        $config = ConfigManager::getLrsConfig();
        $this->assertEquals('', $config['endpoint']);
        $this->assertEquals('', $config['username']);
        $this->assertEquals('', $config['password']);
        $this->assertEquals('basic', $config['auth_method']);
        $this->assertFalse($config['xapi_enabled']);
        $this->assertTrue($config['queue_enabled']);
    }

    /**
     * Test empty credential handling
     */
    public function test_empty_credentials() {
        // Test with empty values.
        ConfigManager::setLrsUsername('');
        ConfigManager::setLrsPassword('');

        $this->assertEquals('', ConfigManager::getLrsUsername());
        $this->assertEquals('', ConfigManager::getLrsPassword());
    }

    /**
     * Test default configuration values
     */
    public function test_default_config() {
        $defaults = ConfigManager::getDefaultConfig();

        $this->assertEquals('', $defaults['endpoint']);
        $this->assertEquals('', $defaults['username']);
        $this->assertEquals('', $defaults['password']);
        $this->assertEquals('basic', $defaults['auth_method']);
        $this->assertFalse($defaults['xapi_enabled']);
        $this->assertTrue($defaults['queue_enabled']);
    }
}