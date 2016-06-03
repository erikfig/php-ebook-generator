<?php

/** Gerador de livros em PDF */

require 'vendor/autoload.php';

/**
 * O componente aceita strings, então você pode puxar os dados do banco de dados
 * ou diretamente de um arquivo local, ou até escrever em uma variável para incluir,
 * nestes exemplos estou utilizando o file_get_contents() para ter acesso ao conteúdo
 * de um arquivo local, mas qualquer string vai bem ali.
 */

//Eu uso esta entidade para construir a estrutura do meu livro
$book = new WebDevBr\Ebook\BookEntity;

//adiciono a capa
$book->addCover(file_get_contents('example/cover.md'));

/**
 * Uma informação a ser exibida antes do livro começar
 * por exemplo: sobre o autor ou prefacio
 * não aparece no indice
 */
$book->addBefore(file_get_contents('example/intro.md'));

//adiciono os capítulos do livro
$book->addChapter(file_get_contents('example/cap1.md'));
$book->addChapter(file_get_contents('example/cap2.md'));

/**
 * E algo que eu queira mostrar no final do livro
 * por exemplo: bibliogragia
 * não aparece no indice
 */
$book->addAfter(file_get_contents('example/encerramento.md'));

/**
 * Aqui eu configuro o domPdf da forma que eu precisar.
 * Você pode encapsular daqui pra frente em uma classe
 * para reutilizar rapidamente.
 */
$dompdf = new Dompdf\Dompdf;
$dompdf->setPaper('A4', 'portrait');
$dompdf->set_option('defaultFont', 'Helvetica');
$dompdf->set_option('dpi', 120);

/**
 * Ccomeço a gerar o pdf injetando o livro que já montei
 * e também o dompdf
 */
//
$generate = new WebDevBr\Ebook\Generate($book, $dompdf);

//opcionalmente um pouco de css pra personalizar a capa
$css = '
	@page :first {
	   margin: 0;
	   padding: 0;
	   border: none;
	}
	
	.container-cover-0 {
		background-color:#c0392b;
		height: 100%;
	}
';
$generate->setCss($css);

//opcionalmente altero o padrão da numeração de páginas do rodapé
$generate->setPageFooter('Página $current_page de $total_pages - www.webdevbr.com.br', 400);

/**
 * Finalmente crio o livro
 * Se o segundo parametro for true (padrão é false)
 * um arquivo html será gerado também.
 */
$generate->make('book.pdf', true);