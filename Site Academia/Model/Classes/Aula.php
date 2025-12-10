<?php

class Aula {

    // Atributos
    private $idAula;
    private $titulo;
    private $descricao;
    private $duracao;
    private $dataCriacao;
    private $idProfessor;
    private $categoria;
    private $ativo;

    public function __construct(
        $idAula = null,
        $titulo = null,
        $descricao = null,
        $duracao = null,
        $dataCriacao = null,
        $idProfessor = null,
        $categoria = null,
        $ativo = null
    ) {
        $this->idAula = $idAula;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->duracao = $duracao;
        $this->dataCriacao = $dataCriacao;
        $this->idProfessor = $idProfessor;
        $this->categoria = $categoria;
        $this->ativo = $ativo;
    }

    // GETTERS e SETTERS

    public function getIdAula() {
        return $this->idAula;
    }

    public function setIdAula($idAula) {
        $this->idAula = $idAula;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function setTitulo($titulo) {
        $this->titulo = $titulo;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function getDuracao() {
        return $this->duracao;
    }

    public function setDuracao($duracao) {
        $this->duracao = $duracao;
    }

    public function getDataCriacao() {
        return $this->dataCriacao;
    }

    public function setDataCriacao($dataCriacao) {
        $this->dataCriacao = $dataCriacao;
    }

    public function getIdProfessor() {
        return $this->idProfessor;
    }

    public function setIdProfessor($idProfessor) {
        $this->idProfessor = $idProfessor;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    public function setCategoria($categoria) {
        $this->categoria = $categoria;
    }

    public function isAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }
}
