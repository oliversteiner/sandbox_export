<?php

namespace Drupal\sandbox_export\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PdfTestController.
 */
class ExportPdfController extends ControllerBase
{
  /**
   * Name of our module.
   *
   * @return string
   *   A module name.
   */
  public function getModuleName(): string
  {
    return 'sandbox_export';
  }

  /**
   * Hello.
   *
   * @param $name
   * @return array
   */
  public function hello($name): array
  {
    return [
      '#theme' => 'sandbox_export',
      '#hallo' => $this->t('Hallo'),
      '#name2' => $name,
    ];
  }

  /**
   * Return default headers (may be overridden by the generator).
   *
   * @param string $filename
   *   The filename to suggest to the browser.
   * @param bool $download
   *   Whether to download the PDF or display it in the browser.
   *
   * @return array
   *   Default headers for the response object.
   */
  private function getHeaders($filename, $download): array
  {
    $disposition = $download ? 'attachment' : 'inline';
    return [
      'Content-Type' => Unicode::mimeHeaderEncode('application/pdf'),
      'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
      'Content-Length' => filesize($filename),
      'Content-Transfer-Encoding' => 'binary',
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
      'Accept-Ranges' => 'bytes',
    ];
  }

  private function getData(): array
  {
    return ['ein', 'zwei', 'drei', 'polizei' => 'hui'];
  }

  private function buildHtml(): array
  {
    $data = $this->getData();
    // HTML
    $template =
      drupal_get_path('module', $this->getModuleName()) .
      '/templates/pdf-view.html.twig';

    $template = file_get_contents($template);

    $build_html = [
      'description' => [
        '#type' => 'inline_template',
        '#template' => $template,
        '#context' => $data,
      ],
    ];

    // Render Twig Template
    return $build_html;
  }

  public function Pdf($mode = false)
  {
    // Configure Dompdf according to your needs
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');

    // Instantiate Dompdf with our options
    $dompdf = new Dompdf($pdfOptions);

    // Retrieve the HTML generated in our twig file
    $html = $this->buildHtml();

    // Load HTML to Dompdf
    $html = \Drupal::service('renderer')->render($html);


    if ($mode === 'test') {
      return new Response($html);

    } else {

      $dompdf->loadHtml($html);

      // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
      $dompdf->setPaper('A4', 'portrait');

      // Render the HTML as PDF
      $dompdf->render();

      // Store PDF Binary Data
      $output = $dompdf->output();


      $filename = 'mypdf.pdf';

      $publicDirectory = \Drupal::service('file_system')->realpath(
        file_default_scheme() . '://'
      );

      $filepath = $publicDirectory . '/' . $filename;

      // Write file to the desired path
      file_put_contents($filepath, $output);
      $save_file = true;

      return (new BinaryFileResponse(
        $filepath,
        200,
        $this->getHeaders($filename, $save_file)
      ))->deleteFileAfterSend(true);
    }
  }
}
