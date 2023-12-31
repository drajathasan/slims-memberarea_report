<?php
use SLiMS\Pdf\Factory;
use SLiMS\Pdf\Contract;
use Dompdf\Dompdf;

final class SlimsPdf extends Contract
{
    public function setPdf():void
    {
        $this->pdf = new Dompdf();
    }

    public function setContent(array $data = []):self
    {
        $this->pdf->loadHtml($data['html']);
        return $this;
    }
    
    public function download(string $filename):void
    {
        $this->stream($filename, ['Attachment' => true]);
    }
    
    public function stream(?string $filename = null, ?array $options = null):void
    {
        $this->pdf->render();
        $this->pdf->stream(($filename??md5('this') . 'pdf'), ($options??['Attachment' => false]));
        exit;
    }
    
    public function saveToFile(string $filepath, ?Closure $callback = null):void
    {
        $this->pdf->render();
        if ($callback !== null) {
            $callback($this->pdf, $filepath);
        } else {
            file_put_contents($filepath, $this->pdf->output());
            exit;
        }
    }
}