<?php

namespace Drupal\geshifilter;

use Drupal\Core\File\FileSystemInterface;
// Necessary for return response of generateCss().
use Symfony\Component\HttpFoundation\Response;

// Necessary for URL.
use Drupal\Core\Url;

/**
 * Helper functions to work with css.
 *
 * All function in this are static, they help with the css generation.
 */
class GeshiFilterCss {

  /**
   * Create the page that show the css in use.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Return the css to show.
   */
  public static function generateCss() {
    $headers = [];
    $headers['Content-type'] = 'text/css';
    $css = self::generateLanguagesCssRules();
    $response = new Response($css, 200, $headers);
    return $response;
  }

  /**
   * Helper for checking if an automatically managed style sheet is possible.
   *
   * @return bool
   *   Indicating if an automatically managed style sheet is possible.
   */
  public static function managedExternalStylesheetPossible() {
    $directory = self::languageCssPath(TRUE);
    return \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
  }

  /**
   * Get the path for css file.
   *
   * @param bool $dironly
   *   TRUE if wants only the dir, FALSE for the full path + file.
   *
   * @return string
   *   Full path to css file.
   */
  public static function languageCssPath($dironly = FALSE) {
    $directory = \Drupal::config('system.file')->get('default_scheme') . '://geshi';
    if (!$dironly) {
      $directory .= '/geshifilter-languages.css';
    }
    return $directory;
  }

  /**
   * Helper function for generating the CSS rules.
   *
   * @return string
   *   String with the CSS rules.
   */
  public static function generateLanguagesCssRules() {
    $output = '';
    $geshi_library = GeshiFilter::loadGeshi();
    if ($geshi_library['loaded']) {
      $languages = GeshiFilter::getEnabledLanguages();
      foreach ($languages as $langcode => $language_full_name) {
        // Create GeSHi object.
        $geshi = GeshiFilterProcess::geshiFactory('', $langcode);
        GeshiFilterProcess::overrideGeshiDefaults($geshi, $langcode);
        // Add CSS rules for current language.
        $output .= $geshi->get_stylesheet(FALSE) . "\n";
        // Release GeSHi object.
        unset($geshi);
      }
    }
    else {
      \Drupal::messenger()->addError(t('Error while generating CSS rules: could not load GeSHi library.'));
    }
    return $output;
  }

  /**
   * Function for generating the external stylesheet.
   *
   * @param bool $force
   *   Force the regeneration of the CSS file.
   */
  public static function generateLanguagesCssFile($force = FALSE) {
    $languages = GeshiFilter::getEnabledLanguages();
    // Serialize the array of enabled languages as sort of hash.
    $languages_hash = serialize($languages);

    // Check if generation of the CSS file is needed.
    if ($force || $languages_hash != \Drupal::state()->get('geshifilter_cssfile_languages')) {
      // Build stylesheet.
      $stylesheet = self::generateLanguagesCssRules();
      // Save stylesheet.
      $stylesheet_filename = self::languageCssPath();

      $ret = \Drupal::service('file.repository')->writeData($stylesheet, $stylesheet_filename, FileSystemInterface::EXISTS_REPLACE);
      if ($ret) {
        \Drupal::messenger()->addStatus(t('(Re)generated external CSS style sheet %file.', ['%file' => $ret->getFilename()]));
      }
      else {
        \Drupal::messenger()->addError(t('Could not generate external CSS file. Check the settings of your <a href="!filesystem">file system</a>.',
          [
            '!filesystem' => Url::fromRoute('system.file_system_settings')->toString(),
          ]));
      }
      // Remember for which list of languages the CSS file was generated.
      \Drupal::state()->set('cssfile_languages', $languages_hash);
    }
  }

}
