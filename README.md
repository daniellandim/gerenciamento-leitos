# 🏥 Hospital Bed Management API

API RESTful para gerenciamento de leitos hospitalares, desenvolvida com **PHP 8.4 + Laravel 12 + PostgreSQL**, containerizada com **Docker**.

---

## 📋 Funcionalidades

| Funcionalidade | Método | Endpoint |
|---|---|---|
| Listar leitos com status | `GET` | `/api/beds` |
| Status de um leito | `GET` | `/api/beds/{id}/status` |
| Internar paciente | `POST` | `/api/beds/{id}/admit` |
| Desocupar leito | `POST` | `/api/beds/{id}/discharge` |
| Transferir paciente | `POST` | `/api/beds/{id}/transfer` |
| Buscar leito por CPF | `GET` | `/api/patients/search?cpf={cpf}` |

---

## 🚀 Como executar

### Pré-requisitos

- [Docker](https://www.docker.com/) instalado
- [Docker Compose](https://docs.docker.com/compose/) instalado

### Passo a passo

**1. Clone o repositório**
```bash
git clone <url-do-repositorio>
cd hospital-beds
```

**2. Configure as variáveis de ambiente**
```bash
cp .env.example .env
```

**3. Suba os containers**
```bash
docker compose up -d --build
```

**4. Aguarde o banco de dados inicializar e rode as migrations**
```bash
docker compose exec app php artisan migrate
```

**5. (Opcional) Popule o banco com dados de exemplo**
```bash
docker compose exec app php artisan db:seed
```

A API estará disponível em: **http://localhost:8080/api**

---

## 🧪 Rodando os testes

```bash
docker compose exec app php artisan test
```

Para ver detalhes de cada teste:
```bash
docker compose exec app php artisan test --verbose
```

---

## 📡 Exemplos de uso

### Listar todos os leitos
```bash
curl http://localhost:8080/api/beds
```

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "identifier": "UTI-01",
      "description": "UTI Adulto",
      "status": "occupied",
      "patient": {
        "id": 1,
        "name": "João da Silva",
        "cpf": "12345678901",
        "admitted_at": "2024-01-15T10:30:00.000000Z"
      }
    },
    {
      "id": 2,
      "identifier": "UTI-02",
      "description": "UTI Adulto",
      "status": "available",
      "patient": null
    }
  ]
}
```

---

### Status de um leito
```bash
curl http://localhost:8080/api/beds/1/status
```

---

### Internar um paciente
```bash
curl -X POST http://localhost:8080/api/beds/1/admit \
  -H "Content-Type: application/json" \
  -d '{"cpf": "12345678901", "name": "João da Silva"}'
```

> ℹ️ Se o paciente não existir no banco, ele será criado automaticamente com o CPF e nome informados.

**Resposta (201):**
```json
{
  "message": "Paciente internado com sucesso.",
  "data": {
    "id": 1,
    "bed": { "id": 1, "identifier": "UTI-01" },
    "patient": { "id": 1, "name": "João da Silva", "cpf": "12345678901" },
    "admitted_at": "2024-01-15T10:30:00.000000Z",
    "discharged_at": null
  }
}
```

---

### Desocupar um leito
```bash
curl -X POST http://localhost:8080/api/beds/1/discharge
```

---

### Transferir paciente para outro leito
```bash
curl -X POST http://localhost:8080/api/beds/1/transfer \
  -H "Content-Type: application/json" \
  -d '{"target_bed_id": 3}'
```

---

### Buscar leito por CPF do paciente
```bash
curl http://localhost:8080/api/patients/search?cpf=12345678901
```

**Resposta:**
```json
{
  "data": {
    "patient": { "id": 1, "name": "João da Silva", "cpf": "12345678901" },
    "bed": { "id": 1, "identifier": "UTI-01", "description": "UTI Adulto" },
    "admitted_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

---

## ⚠️ Regras de negócio

- Um paciente **não pode estar em mais de um leito** ao mesmo tempo
- Cada leito **suporta apenas um paciente** por vez
- Tentativas de violar essas regras retornam **HTTP 409 Conflict** com mensagem descritiva
- O histórico de todas as internações é preservado no banco (auditoria)

---

## 🗄️ Estrutura do banco de dados

```
beds
├── id
├── identifier  (único, ex: "UTI-01")
├── description (opcional)
└── timestamps

patients
├── id
├── name
├── cpf         (único, 11 dígitos)
└── timestamps

bed_occupancies
├── id
├── bed_id      (FK → beds)
├── patient_id  (FK → patients)
├── admitted_at
├── discharged_at (NULL = internação ativa)
└── timestamps
```

---

## 🏗️ Arquitetura

```
app/
├── Http/Controllers/
│   ├── BedController.php      # Endpoints de leitos
│   └── PatientController.php  # Busca por CPF
├── Models/
│   ├── Bed.php
│   ├── Patient.php
│   └── BedOccupancy.php
└── Services/
    └── BedService.php         # Regras de negócio isoladas
```

---

## 🛑 Parando os containers

```bash
docker compose down
```

Para remover também os volumes (dados do banco):
```bash
docker compose down -v
```
