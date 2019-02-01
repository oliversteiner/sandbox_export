<?php
/**
 * Created by PhpStorm.
 * User: ost
 * Date: 2019-02-01
 * Time: 10:04
 *
 *
 * https://ourcodeworld.com/articles/read/798/how-to-create-an-excel-file-with-php-in-symfony-4
 */

namespace Drupal\sandbox_export\Controller;


use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

// Include PhpSpreadsheet required namespaces
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportXlsxController extends ControllerBase
{
  /**
   * @return mixed
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
   */

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
  private function getHeaders($filename, $download, $mimeHeader): array
  {
    $disposition = $download ? 'attachment' : 'inline';
    return [
      'Content-Type' => Unicode::mimeHeaderEncode($mimeHeader),
      'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
      'Content-Length' => filesize($filename),
      'Content-Transfer-Encoding' => 'binary',
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
      'Accept-Ranges' => 'bytes',
    ];
  }

  /**
   * @param $mode
   * @return BinaryFileResponse
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
   */
  public function export($mode)
  {
    $spreadsheet = new Spreadsheet();

    /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World !');
    $sheet->setTitle("My First Worksheet");

    // Create your Office 2007 Excel (XLSX Format)
    $writer = new Xlsx($spreadsheet);

    // Create a Temporary file in the system
    $fileName = 'my_first_excel_symfony4.xlsx';
    $temp_file = tempnam(sys_get_temp_dir(), $fileName);

    // Create the excel file in the tmp directory of the system
    $writer->save($temp_file);

    // Return the excel file as an attachment
   //  return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);



    $publicDirectory = \Drupal::service('file_system')->realpath(
      file_default_scheme() . '://'
    );

    $filepath = $publicDirectory . '/' . $fileName;
    copy($temp_file, $filepath );

    // Write file to the desired path
  //  file_put_contents($filepath, $output);
    $save_file = true;
    $mimeHeader = 'application/xlsx';

    return (new BinaryFileResponse(
      $filepath,
      200,
      $this->getHeaders($fileName, $save_file, $mimeHeader)
    ))->deleteFileAfterSend(true);
  }
}
