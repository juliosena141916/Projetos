<?php

class Usuario {

    // Atributos
    private $idUsuario;
    private $nome;
    private $email;
    private $senha;
    private $cpf;
    private $dataNascimento;
    private $telefone;
    private $fotoPerfil;        // URL ou caminho da imagem
    private $tipo;              // admin, aluno, professor
    private $dataCadastro;
    private $ultimoAcesso;
    private $status;            // ativo, banido, pendente

    public function __construct(
        $idUsuario = null,
        $nome = null,
        $email = null,
        $senha = null,
        $cpf = null,
        $dataNascimento = null,
        $telefone = null,
        $fotoPerfil = null,
        $tipo = null,
        $dataCadastro = null,
        $ultimoAcesso = null,
        $status = null
    ) {
        $this->idUsuario = $idUsuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->cpf = $cpf;
        $this->dataNascimento = $dataNascimento;
        $this->telefone = $telefone;
        $this->fotoPerfil = $fotoPerfil;
        $this->tipo = $tipo;
        $this->dataCadastro = $dataCadastro;
        $this->ultimoAcesso = $ultimoAcesso;
        $this->status = $status;
    }

    // GETTERS E SETTERS

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    public function getDataNascimento() {
        return $this->dataNascimento;
    }

    public function setDataNascimento($dataNascimento) {
        $this->dataNascimento = $dataNascimento;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    public function getFotoPerfil() {
        return $this->fotoPerfil;
    }

    public function setFotoPerfil($fotoPerfil) {
        $this->fotoPerfil = $fotoPerfil;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function getDataCadastro() {
        return $this->dataCadastro;
    }

    public function setDataCadastro($dataCadastro) {
        $this->dataCadastro = $dataCadastro;
    }

    public function getUltimoAcesso() {
        return $this->ultimoAcesso;
    }

    public function setUltimoAcesso($ultimoAcesso) {
        $this->ultimoAcesso = $ultimoAcesso;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
}
