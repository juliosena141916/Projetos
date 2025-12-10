<?php

class Matricula {

    // Atributos
    private $idMatricula;
    private $idAluno;
    private $idCurso;
    private $dataMatricula;
    private $status;           // ativo, cancelado, concluído
    private $progresso;        // porcentagem concluída
    private $valorPago;
    private $formaPagamento;   // cartão, pix, boleto
    private $dataConclusao;


    public function __construct(
        $idMatricula = null,
        $idAluno = null,
        $idCurso = null,
        $dataMatricula = null,
        $status = null,
        $progresso = null,
        $valorPago = null,
        $formaPagamento = null,
        $dataConclusao = null
    ) {
        $this->idMatricula = $idMatricula;
        $this->idAluno = $idAluno;
        $this->idCurso = $idCurso;
        $this->dataMatricula = $dataMatricula;
        $this->status = $status;
        $this->progresso = $progresso;
        $this->valorPago = $valorPago;
        $this->formaPagamento = $formaPagamento;
        $this->dataConclusao = $dataConclusao;
    }

    // GETTERS E SETTERS

    public function getIdMatricula() {
        return $this->idMatricula;
    }

    public function setIdMatricula($idMatricula) {
        $this->idMatricula = $idMatricula;
    }

    public function getIdAluno() {
        return $this->idAluno;
    }

    public function setIdAluno($idAluno) {
        $this->idAluno = $idAluno;
    }

    public function getIdCurso() {
        return $this->idCurso;
    }

    public function setIdCurso($idCurso) {
        $this->idCurso = $idCurso;
    }

    public function getDataMatricula() {
        return $this->dataMatricula;
    }

    public function setDataMatricula($dataMatricula) {
        $this->dataMatricula = $dataMatricula;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getProgresso() {
        return $this->progresso;
    }

    public function setProgresso($progresso) {
        $this->progresso = $progresso;
    }

    public function getValorPago() {
        return $this->valorPago;
    }

    public function setValorPago($valorPago) {
        $this->valorPago = $valorPago;
    }

    public function getFormaPagamento() {
        return $this->formaPagamento;
    }

    public function setFormaPagamento($formaPagamento) {
        $this->formaPagamento = $formaPagamento;
    }

    public function getDataConclusao() {
        return $this->dataConclusao;
    }

    public function setDataConclusao($dataConclusao) {
        $this->dataConclusao = $dataConclusao;
    }
}
