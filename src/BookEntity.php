<?php

namespace WebDevBr\Ebook;

class BookEntity
{
	protected $cover = [];
	protected $before = [];
	protected $chapters = [];
	protected $after = [];

	public function addCover(string $text) {
		$this->cover = [$text];
	}

	public function addBefore(string $text) {
		$this->before[] = $text;
	}

	public function addChapter(string $text) {
		$this->chapters[] = $text;
	}

	public function addAfter(string $text) {
		$this->after[] = $text;
	}

	public function __get($name)
	{
		return $this->$name;
	}
}