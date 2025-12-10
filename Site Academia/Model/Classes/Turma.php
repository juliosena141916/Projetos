<?php

class TurmaDAO {

    // Atributos
    private $idTurma;
    private $nome;
    private $idCurso;
    private $idProfessor;
    private $dataInicio;
    private $dataFim;
    private $horario;           // Ex: 19:00 - 21:00
    private $capacidade;        // Nº máximo de alunos
    private $status;            // aberta, fechada, encerrada
    private $local;             // sala física ou online
    private $ativo;

    public function __construct(
        $idTurma = null,
        $nome = null,
        $idCurso = null,
        $idProfessor = null,
        $dataInicio = null,
        $dataFim = null,
        $horario = null,
        $capacidade = null,
        $status = null,
        $local = null,
        $ativo = null
    ) {
        $this->idTurma = $idTurma;
        $this->nome = $nome;
        $this->idCurso = $idCurso;
        $this->idProfessor = $idProfessor;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->horario = $horario;
        $this->capacidade = $capacidade;
        $this->status = $status;
        $this->local = $local;
        $this->ativo = $ativo;
    }

    // GETTERS E SETTERS

    public function getIdTurma() {
        return $this->idTurma;
    }

    public function setIdTurma($idTurma) {
        $this->idTurma = $idTurma;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getIdCurso() {
        return $this->idCurso;
    }

    public function setIdCurso($idCurso) {
        $this->idCurso = $idCurso;
    }

    public function getIdProfessor() {
        return $this->idProfessor;
    }

    public function setIdProfessor($idProfessor) {
        $this->idProfessor = $idProfessor;
    }

    public function getDataInicio() {
        return $this->dataInicio;
    }

    public function setDataInicio($dataInicio) {
        $this->dataInicio = $dataInicio;
    }

    public function getDataFim() {
        return $this->dataFim;
    }

    public function setDataFim($dataFim) {
        $this->dataFim = $dataFim;
    }

    public function getHorario() {
        return $this->horario;
    }

    public function setHorario($horario) {
        $this->horario = $horario;
    }

    public function getCapacidade() {
        return $this->capacidade;
    }

    public function setCapacidade($capacidade) {
        $this->capacidade = $capacidade;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getLocal() {
        return $this->local;
    }

    public function setLocal($local) {
        $this->local = $local;
    }

    public function isAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }
}
