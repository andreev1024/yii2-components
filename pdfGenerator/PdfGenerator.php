<?php

namespace andreev1024\pdfGenerator;

use kartik\mpdf\Pdf;
use Yii;

/**
 * This class generate (render) Pdf document.
 */
class PdfGenerator
{
    /**
     * Render Twig template
     * @author Andreev <andreev1024@gmail.com>
     * @version ver 1.0 added on 2015-05-07
     * @access  private
     * @param   string $template html code
     * @param   array $data Data for template variable
     * @param   array $options
     * @return  string
     */
    public function renderTemplate($template, array $data, array $options)
    {
        $this->renderBarcode($data, $options);
        $this->selectiveSkipEscaping($template);
        return Yii::$app->twig->getInstance()->loadTemplate($template)->render($data);
    }

    /**
     * Render Pdf document.
     * @author Andreev <andreev1024@gmail.com>
     * @version ver 1.0 added on 2015-05-07
     * @access  public
     *
     * @param   string $template
     * @param   array $data Data for template variable
     * @param   string $destination The PDF output destination (download, show in browser etc.)
     * @param   array $options
     *
     * @return mixed
     */
    public function renderPdf($template, array $data, $destination, array $options = [])
    {
        $defaultOptions = [
            'fileName' => str_replace([' ', '.'], '', microtime()) . '.pdf',
            'header' => null,
            'footer' => null,
            'showBarcode' => null,
            'barcodeType' => null,
            'language' => Yii::$app->language,
            'css' => '',
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
        ];

        $options = array_merge($defaultOptions, $options);

        $destination = strtoupper($destination);
        if (!$destination = $this->getDestination($destination)) {
            return false;
        }

        $header = $footer = null;
        if (isset($options['header'])) {
            $header = $this->renderTemplate($options['header'], $data, $options);
        }
        if (isset($options['footer'])) {
            $footer = $this->renderTemplate($options['footer'], $data, $options);
        }

        $content = $this->renderTemplate($template, $data, $options);

        if ($options['language'] == 'ja') {
            $options['css'] = "body {font-family: 'msgothic, SJIS, sans-serif'} " . $options['css'];
        }

        $config = [
            // set to use core fonts only
            // 'mode' => Pdf::MODE_CORE,
            'format' => $options['format'],
            'orientation' => $options['orientation'],
            'destination' => $destination,
            'content' => $content,
            'cssInline' => $options['css'],
            // set mPDF properties on the fly
            'options' => [
                'title' => $options['title'],
                //  http://mpdf1.com/manual/index.php?tid=507
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
            ],
            // call mPDF methods on the fly
            'methods' => [
                'SetHeader' => [$header],
                'SetFooter' => [$footer],
            ],
            'marginTop' => 26,
            'filename' => $options['fileName'],
        ];

        $pdf = new Pdf($config);
        return $pdf->render();
    }

    /**
     * Barcode rendering.
     * @author Andreev <andreev1024@gmail.com>
     * @version ver 1.0 added on 2015-05-07
     * @access  private
     * @param   array &$data Data for template variable
     * @param   array $options
     * @return  boolean
     * @todo Skiped params: height, text, pr (http://mpdf1.com/manual/index.php?tid=407)
     */
    private function renderBarcode(array &$data, array $options)
    {
        $showBarcode = isset($options['showBarcode']) ? $options['showBarcode'] : null;
        $barcodeType = isset($options['barcodeType']) ? $options['barcodeType'] : null;

        if (!$showBarcode || !isset($data['barcode']) || !$barcodeType) {
            $data['barcode'] = '';
            return false;
        }

        $data['barcode'] = '<barcode code="' . $data['barcode'] . '" type="' . $options['barcodeType'] . '" />';
        
        return true;
    }

    /**
     * Allows skip ecaping for some twig varibles
     * @author Andreev <andreev1024@gmail.com>
     * @version ver 1.0 added on 2015-05-07
     * @param $template
     * @access  protected
     * @return  null
     */
    protected function selectiveSkipEscaping(&$template)
    {
        $tags = [
            'barcode',
        ];

        $tags = implode('|', $tags);
        $template = preg_replace("#{{\s*({$tags})\s*}}#i", '{{$1|raw}}', $template);
    }

    /**
     * Transform url param `destination` (e.g. 'download')
     * in mpdf destination (e.g 'D')
     * @author Andreev <andreev1024@gmail.com>
     * @version ver 1.0 added on 2015-05-07
     * @access  public
     * @param   string $destination
     * @return  mixed
     */
    public function getDestination($destination)
    {
        $destination = strtolower($destination);
        $availableDestination = [
            'preview' => Pdf::DEST_BROWSER,
            'download' =>  Pdf::DEST_DOWNLOAD,
            'file' =>  Pdf::DEST_FILE,
            'string' =>  Pdf::DEST_STRING,
            'i' => Pdf::DEST_BROWSER,
            'd' =>  Pdf::DEST_DOWNLOAD,
            'f' =>  Pdf::DEST_FILE,
            's' =>  Pdf::DEST_STRING
        ];

        if (isset($availableDestination[$destination])) {
            return $availableDestination[$destination];
        }

        return false;
    }
}