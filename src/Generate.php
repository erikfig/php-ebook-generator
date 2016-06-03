<?php

namespace WebDevBr\Ebook;

use Michelf\Markdown;
use Dompdf\Dompdf;

class Generate
{
	protected $book;
	protected $domPdf;
	protected $css = '<style></style>';
	protected $toc_total = [];
	protected $pageFooter = 'Pag $current_page of $total_pages';
	protected $pageFooterHorizontal = 500;

	public function __construct(BookEntity $book, Dompdf $domPdf)
	{
		$this->book=$book;
		$this->domPdf=$domPdf;
		$this->domPdf->set_option('isPhpEnabled', true);
	}

	public function setCss(string $css)
	{
		$this->css = '<style>'.$css.'</style>';
	}

	public function setPageFooter(string $pageFooter, int $pageFooterHorizontal = 500)
	{
		$this->pageFooter = $pageFooter;
		$this->pageFooterHorizontal = $pageFooterHorizontal;
	}

	public function make(string $destination, bool $debug = false)
	{
		$cover = $this->toMarkdown($this->book->cover, 'cover');
		$before = $this->toMarkdown($this->book->before, 'before');
		$chapters = $this->toMarkdown($this->book->chapters, 'chapter');
		$after = $this->toMarkdown($this->book->after, 'after');

		$book = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'
			.$this->css
			.'</head><body>'
			.$cover
			.$before
			.$this->tocTemplate()
			.$chapters
			.$after
			.'</body></html>';

		$this->domPdf->loadHtml($book,'UTF-8');

		$this->domPdf->render();

		$this->toc();
		$this->pageNumber();

		file_put_contents($destination, $this->domPdf->output());
		if ($debug)
			file_put_contents($destination.'.html', $book);

	}

	protected function toMarkdown($data, $container)
	{
		foreach ($data as $k => &$v) {
			$v = Markdown::defaultTransform($v.PHP_EOL);
			if ($container == 'chapter')
				$this->insertToc($v);
			$v = "<div class=\"container container-$container container-$container-$k\" style=\"page-break-after: always;\">$v</div>";
			
		}

		return implode('', $data);
	}

	protected function pageNumber()
	{
		$canvas = $this->domPdf->getCanvas();
		$canvas->page_script('
		  if ($PAGE_NUM > 1) {
		    $current_page = $PAGE_NUM-1;
		    $total_pages = $PAGE_COUNT-1;
		    $pdf->text('.$this->pageFooterHorizontal.', 800, "'.$this->pageFooter.'", null, 10, array(0,0,0));
		  }
		');
	}

	protected function insertToc(&$text)
	{
		preg_match_all('/<h[0-3].*?>.*?<\/h[0-3]>/i', $text, $matches);
		
		foreach ($matches[0] as $v) {
			preg_match('/<h[0-3].*?>/i', $v, $level);
			$level = preg_replace("/[^0-9]/","",$level)[0];
			$this->toc_total[] = $level;
			$attachment = '<script type="text/php"> 
				if ($pdf) { 
				  $GLOBALS[\'chapters\'][] = [
				  	\'n\' => $pdf->get_page_number() - 1,
				  	\'t\' => "'.strip_tags($v).'"
				  ];
				}
			</script>';
			$text = str_replace($v, $v.$attachment, $text);
		};
	}

	protected function tocTemplate()
	{
		$toc = '
<script type="text/php">
	$GLOBALS[\'chapters\'] = array();
	$GLOBALS[\'backside\'] = $pdf->open_object();
	$GLOBALS[\'toc_page\'] = $pdf->get_page_number();
</script>

## Indice
';
 	for ($i=0; $i < count($this->toc_total); $i++) { 
 		$toc .= ' '.str_repeat(" ", $this->toc_total[$i]).'- %%TOC'.$i.'%%'.PHP_EOL;
 	}
	
	$toc .= '
<script type="text/php">
	$pdf->close_object();
</script>
';
		$toc = Markdown::defaultTransform($toc.PHP_EOL);

		return '<div class=\"container container-toc" style="page-break-after: always;">'.$toc.'</div>';
	}

	protected function toc()
	{
		$canvas = $this->domPdf->getCanvas();
		$canvas->page_script('
			foreach ($GLOBALS[\'chapters\'] as $chapter => $page) {
				$page_number = $page[\'n\'];
				$title = html_entity_decode($page[\'t\']);
				$toc = $title." - Pág. ".$page_number.PHP_EOL;
				$toc = utf8_decode($toc);

				$backside_c = $pdf->get_cpdf()->objects[$GLOBALS[\'backside\']][\'c\'];
				$pdf->get_cpdf()->objects[$GLOBALS[\'backside\']][\'c\'] = str_replace( \'%%TOC\'.$chapter.\'%%\' , $toc , $backside_c);
			}
			
			$pdf->page_script(\'
				if ($PAGE_NUM==$GLOBALS["toc_page"]) {
					$pdf->add_object($GLOBALS["backside"],"add");
					$pdf->stop_object($GLOBALS["backside"]);
				}
			\');
		');
	}
}