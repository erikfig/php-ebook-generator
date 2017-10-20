# PHP Ebook Generator

PHP Ebook Generator é um gerador de livros em PDF que aceita Markdown, atualmente está em desenvolvimento, mas adianto que já usei para gerar propostas de orçamento e apostilas e está bem funcional.

## Instalação

Para instalar [baixe o zip](https://github.com/erikfig/php-ebook-generator/releases) ou integre ao seu sistema usando Composer:

	composer require webdevbr/php-ebook-generator dev-master

## Uso

Você cria o ebook usando uma entidade, em seguida configura o Dompdf e injeta ele no objeto responsável por gerar o PDF, o comando `make` finaliza o processo.

Exemplo, crie um arquivo chamado example.php e configure conforme a seguir, por fim rode no terminal (ou cmd).

Aqui neste repositório tem exatamente este exemplo pronto para executar, é so clonar (ou baixar) e rodar `php example.php` na raiz do projeto.


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


Não esqueçam de criar os arquivos dentro de um diretório example:

## Para contribuir

Crie uma issue ou veja o quem tem lá disponível para você fazer, comente que vai fazer ou tire suas dúvidas, fork o projeto e depois das correções feitas faça um pull request de volta.

Simples assim