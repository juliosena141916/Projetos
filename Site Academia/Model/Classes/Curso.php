<?php

class Curso {

    // Atributos
    private $idCurso;
    private $nome;
    private $descricao;
    private $cargaHoraria;      // em horas
    private $nivel;             // básico, intermediário, avançado
    private $dataLancamento;
    private $valor;
    private $ativo;
    private $idCategoria;


    public function __construct(
        $idCurso = null,
        $nome = null,
        $descricao = null,
        $cargaHoraria = null,
        $nivel = null,
        $dataLancamento = null,
        $valor = null,
        $ativo = null,
        $idCategoria = null
    ) {
        $this->idCurso = $idCurso;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->cargaHoraria = $cargaHoraria;
        $this->nivel = $nivel;
        $this->dataLancamento = $dataLancamento;
        $this->valor = $valor;
        $this->ativo = $ativo;
        $this->idCategoria = $idCategoria;
    }

    // GETTERS e SETTERS

    public function getIdCurso() {
        return $this->idCurso;
    }

    public function setIdCurso($idCurso) {
        $this->idCurso = $idCurso;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function getCargaHoraria() {
        return $this->cargaHoraria;
    }

    public function setCargaHoraria($cargaHoraria) {
        $this->cargaHoraria = $cargaHoraria;
    }

    public function getNivel() {
        return $this->nivel;
    }

    public function setNivel($nivel) {
        $this->nivel = $nivel;
    }

    public function getDataLancamento() {
        return $this->dataLancamento;
    }

    public function setDataLancamento($dataLancamento) {
        $this->dataLancamento = $dataLancamento;
    }

    public function getValor() {
        return $this->valor;
    }

    public function setValor($valor) {
        $this->valor = $valor;
    }

    public function isAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

    public function getIdCategoria() {
        return $this->idCategoria;
    }

    public function setIdCategoria($idCategoria) {
        $this->idCategoria = $idCategoria;
    }
}
