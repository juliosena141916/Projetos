<?php

class Unidade {

    // Atributos
    private $idUnidade;
    private $nome;
    private $endereco;
    private $cidade;
    private $estado;
    private $cep;
    private $telefone;
    private $email;
    private $dataAbertura;
    private $ativo;

    public function __construct(
        $idUnidade = null,
        $nome = null,
        $endereco = null,
        $cidade = null,
        $estado = null,
        $cep = null,
        $telefone = null,
        $email = null,
        $capacidade = null,
        $dataAbertura = null,
        $ativo = null) {

        $this->idUnidade = $idUnidade;
        $this->nome = $nome;
        $this->endereco = $endereco;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->cep = $cep;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->capacidade = $capacidade;
        $this->dataAbertura = $dataAbertura;
        $this->ativo = $ativo;
    }

    // GETTERS E SETTERS

    public function getIdUnidade() {
        return $this->idUnidade;
    }

    public function setIdUnidade($idUnidade) {
        $this->idUnidade = $idUnidade;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function getCep() {
        return $this->cep;
    }

    public function setCep($cep) {
        $this->cep = $cep;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getCapacidade() {
        return $this->capacidade;
    }

    public function setCapacidade($capacidade) {
        $this->capacidade = $capacidade;
    }

    public function getDataAbertura() {
        return $this->dataAbertura;
    }

    public function setDataAbertura($dataAbertura) {
        $this->dataAbertura = $dataAbertura;
    }

    public function isAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }
}
