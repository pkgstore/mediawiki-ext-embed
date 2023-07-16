<?php

namespace MediaWiki\Extension\PkgStore;

use MWException;
use OutputPage, Parser, Skin;
use Embed\{Embed, Http\CurlDispatcher};

/**
 * Class MW_EXT_Embed
 */
class MW_EXT_Embed
{
  /**
   * Register tag function.
   *
   * @param Parser $parser
   *
   * @return void
   * @throws MWException
   */
  public static function onParserFirstCallInit(Parser $parser): void
  {
    $parser->setFunctionHook('embed', [__CLASS__, 'onRenderTag']);
  }

  /**
   * Render tag function.
   *
   * @param Parser $parser
   * @param string $url
   *
   * @return string|null
   */
  public static function onRenderTag(Parser $parser, string $url = ''): ?string
  {
    // Argument: url.
    $getURL = MW_EXT_Kernel::outClear($url ?? '' ?: '');
    $outURL = $getURL;

    // Check URL.
    if (empty($outURL)) {
      $parser->addTrackingCategory('mw-embed-error-category');

      return null;
    }

    $dispatcher = new CurlDispatcher([
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_ENCODING => '',
      CURLOPT_AUTOREFERER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
      CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    ]);

    // Get URL data.
    $getData = Embed::create($outURL, null, $dispatcher);
    $outData = $getData->code;

    // Out HTML.
    $outHTML = '<div class="mw-embed navigation-not-searchable"><div class="mw-embed-body"><div class="mw-embed-content">' . $outData . '</div></div></div>';

    // Out parser.
    return $parser->insertStripItem($outHTML, $parser->getStripState());
  }

  /**
   * Load resource function.
   *
   * @param OutputPage $out
   * @param Skin $skin
   *
   * @return void
   */
  public static function onBeforePageDisplay(OutputPage $out, Skin $skin): void
  {
    $out->addModuleStyles(['ext.mw.embed.styles']);
  }
}
