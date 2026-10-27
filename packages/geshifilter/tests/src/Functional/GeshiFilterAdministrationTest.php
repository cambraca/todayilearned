<?php

namespace Drupal\Tests\geshifilter\Functional;

// Use of base class for the tests.
use Drupal\Tests\BrowserTestBase;

use Drupal\geshifilter\GeshiFilter;

use Drupal\Core\Session\AccountInterface;

/**
 * Test for administrative interface of GeshiFilter.
 *
 * @group geshifilter
 */
class GeshiFilterAdministrationTest extends BrowserTestBase {

  /**
   * A global filter adminstrator.
   *
   * @var object
   */
  protected $filterAdminUser;

  /**
   * The id of the text format with only GeSHi filter in it.
   *
   * @var string
   */
  protected $inputFormatIid;

  /**
   * List of modules to enable.
   *
   * @var array
   */
  protected static $modules = ['geshifilter', 'filter'];

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Theme used in test.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Set up the tests and create the users.
   */
  public function setUp(): void {
    parent::setUp();

    // Create object with configuration.
    $this->config = \Drupal::configFactory()->getEditable('geshifilter.settings');

    // Create a filter admin user.
    $permissions = [
      'administer filters',
      'access administration pages',
      'administer site configuration',
    ];
    $this->filterAdminUser = $this->drupalCreateUser($permissions);

    // Log in with filter admin user.
    $this->drupalLogin($this->filterAdminUser);

    // Add a text format with only geshifilter
    // $this->createTextFormat('geshifilter_text_format',
    // array('filter_geshifilter'));.
    // Set some default GeSHi filter admin settings.
    // Set default highlighting mode to "do nothing".
    $this->config->set('default_highlighting', GeshiFilter::DEFAULT_PLAINTEXT);
    $this->config->set('use_format_specific_options', FALSE);
    $this->config->set('tag_styles', [
      GeshiFilter::BRACKETS_ANGLE => GeshiFilter::BRACKETS_ANGLE,
      GeshiFilter::BRACKETS_SQUARE => GeshiFilter::BRACKETS_SQUARE,
    ]);
    $this->config->set('default_line_numbering', GeshiFilter::LINE_NUMBERS_DEFAULT_NONE);
    $this->config->save();
  }

  /**
   * Tags should differ between languages and from generic tags.
   */
  public function testTagUnicity() {
    // Enable some languages first.
    $this->config->set('language.php.enabled', TRUE);
    $this->config->set('language.python.enabled', TRUE);

    // First round: without format specific tag options.
    $this->config->set('use_format_specific_options', FALSE);
    $this->config->set('tags', 'code blockcode generictag');
    $this->config->save();

    // A language tag should differ from the generic tags.
    $form_values = [
      'language[php][tags]' => 'php generictag',
    ];
    $this->drupalGet('admin/config/content/formats/geshifilter/languages/all');
    $this->submitForm($form_values, t('Save configuration'));
    $this->assertSession()->pageTextContains(t('The language tags should differ between languages and from the generic tags.'));

    // Language tags should differ between languages.
    $form_values = [
      'language[php][tags]' => 'php languagetag',
      'language[python][tags]' => 'languagetag python',
    ];
    $this->drupalGet('admin/config/content/formats/geshifilter/languages/all');
    $this->submitForm($form_values, t('Save configuration'));
    $this->assertSession()->pageTextContains(t('The language tags should differ between languages and from the generic tags.'));

    // Second round: with format specific tag options.
    // $this->config->set('use_format_specific_options', TRUE);
    // $this->drupalPostForm
    // ('admin/config/content/formats/manage/geshifilter_text_format', array(),
    // t('Save configuration'));.
    /*$this->config->set('tags_' . $this->input_format_id,
    'code blockcode generictag');
    // A language tag should differ from the generic tags.
    $form_values = array(
    'geshifilter_language_tags_php_' . $this->input_format_id =>
    'php generictag');
    $this->drupalPostForm('admin/config/content/formats/' .
    $this->input_format_id
    . '/configure', $form_values, t('Save configuration'));
    $this->assertText(t('The language tags should differ between languages
    and from the generic tags.'), t('Language tags should differ from
    (with format specific tag options)'));
    // Language tags should differ between languages.
    $form_values = array(
    'geshifilter_language_tags_php_' . $this->input_format_id =>
    'php languagetag',
    'geshifilter_language_tags_python_' . $this->input_format_id =>
    'languagetag python',
    );
    $this->drupalPostForm('admin/config/content/formats/' .
    $this->input_format_id .
    '/configure', $form_values, t('Save configuration'));
    $this->assertText(t('The language tags should differ between languages
    and from the
    generic tags.'), t('Language tags should differ between languages (with
    format specific tag options)'));*/
  }

  /**
   * Create a new text format.
   *
   * @param string $format_name
   *   The name of new text format.
   * @param array $filters
   *   Array with the machine names of filters to enable.
   */
  protected function createTextFormat($format_name, array $filters) {
    $edit = [];
    $edit['format'] = $format_name;
    $edit['name'] = $this->randomMachineName();
    $edit['roles[' . AccountInterface::AUTHENTICATED_ROLE . ']'] = 1;
    foreach ($filters as $filter) {
      $edit['filters[' . $filter . '][status]'] = TRUE;
    }
    $this->drupalGet('admin/config/content/formats/add');
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()->responseContains(t('Added text format %format.', ['%format' => $edit['name']]));
    $this->drupalGet('admin/config/content/formats');
  }

  /**
   * Tests for GeshiFilterLanguageForm.
   */
  public function testLanguagesForm() {
    $edit = [];
    $edit['language[xml][enabled]'] = TRUE;
    $edit['language[xml][tags]'] = "<xml>";
    $this->drupalGet('admin/config/content/formats/geshifilter/languages/all');
    $this->submitForm($edit, t('Save configuration'));
    $this->drupalGet('admin/config/content/formats/geshifilter/languages/all');
    $this->assertSession()->checkboxChecked('edit-language-xml-enabled');
    $this->assertSession()->responseContains('&lt;xml&gt;');
  }

}
