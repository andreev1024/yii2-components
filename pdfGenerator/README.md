#Pdf-genarator

This component take template (string) and data and:
* return rendered content;
* or  build Pdf document. 

Pdf-genarator based on `kartik-v/yii2-mpdf`.

##Example

```
//  get content like a simple html without barcode
$content = (new PdfGenerator)->renderTemplate(
    $templateModel->template,
    $data,
    ['showBarcode' => null]
);

//  render Pdf document and send it to the browser
(new PdfGenerator)->renderPdf(
    $template->template, 
    $data, 
    Pdf::DEST_BROWSER, 
    $options
);
```