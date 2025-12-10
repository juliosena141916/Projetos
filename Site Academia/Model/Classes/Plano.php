<?php

class Plano {

    // Atributos
    private $idPlano;
    private $nome;
    private $descricao;
    private $valorMensal;
    private $valorAnual;
    private $periodicidade;    // mensal, trimestral, anual
    private $limiteCursos;     // quantidade de cursos permitidos
    private $dataCriacao;
    private $ativo;

    public function __construct(
        $idPlano = null,
        $nome = null,
        $descricao = null,
        $valorMensal = null,
        $valorAnual = null,
        $periodicidade = null,
        $limiteCursos = null,
        $dataCriacao = null,
        $ativo = null
    ) {
        $this->idPlano = $idPlano;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->valorMensal = $valorMensal;
        $this->valorAnual = $valorAnual;
        $this->periodicidade = $periodicidade;
        $this->limiteCursos = $limiteCursos;
        $this->dataCriacao = $dataCriacao;
        $this->ativo = $ativo;
    }

    // GETTERS E SETTERS

    public function getIdPlano() {
        return $this->idPlano;
    }

    public function setIdPlano($idPlano) {
        $this->idPlano = $idPlano;
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

    public function getValorMensal() {
        return $this->valorMensal;
    }

    public function setValorMensal($valorMensal) {
        $this->valorMensal = $valorMensal;
    }

    public function getValorAnual() {
        return $this->valorAnual;
    }

    public function setValorAnual($valorAnual) {
        $this->valorAnual = $valorAnual;
    }

    public function getPeriodicidade() {
        return $this->periodicidade;
    }

    public function setPeriodicidade($periodicidade) {
        $this->periodicidade = $periodicidade;
    }

    public function getLimiteCursos() {
        return $this->limiteCursos;
    }

    public function setLimiteCursos($limiteCursos) {
        $this->limiteCursos = $limiteCursos;
    }

    public function getDataCriacao() {
        return $this->dataCriacao;
    }

    public function setDataCriacao($dataCriacao) {
        $this->dataCriacao = $dataCriacao;
    }

    public function isAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }
}
