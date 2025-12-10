/**
 * TechFit - Unidades
 * Funcionalidades da página de unidades
 */

// Dados das unidades por cidade
const unidadesData = {
  'Limeira': [
    {
      nome: 'TechFit Limeira Centro',
      endereco: 'Av. Campinas, 1234 - Centro, Limeira - SP',
      telefone: '(19) 3451-1234',
      horario: 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'
    },
    {
      nome: 'TechFit Limeira Norte',
      endereco: 'Rua Dr. Trajano de Barros Camargo, 567 - Jardim Nova Limeira, Limeira - SP',
      telefone: '(19) 3451-5678',
      horario: 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'
    }
  ],
  'Campinas': [
    {
      nome: 'TechFit Campinas Centro',
      endereco: 'Av. Francisco Glicério, 890 - Centro, Campinas - SP',
      telefone: '(19) 3234-1111',
      horario: 'Segunda a Sexta: 5h30 às 23h | Sábado: 6h às 21h | Domingo: 7h às 19h'
    },
    {
      nome: 'TechFit Campinas Cambuí',
      endereco: 'Rua Barão de Jaguara, 2345 - Cambuí, Campinas - SP',
      telefone: '(19) 3234-2222',
      horario: 'Segunda a Sexta: 5h30 às 23h | Sábado: 6h às 21h | Domingo: 7h às 19h'
    },
    {
      nome: 'TechFit Campinas Taquaral',
      endereco: 'Av. Nossa Sra. de Fátima, 678 - Jardim Nossa Sra. Auxiliadora, Campinas - SP',
      telefone: '(19) 3234-3333',
      horario: 'Segunda a Sexta: 5h30 às 23h | Sábado: 6h às 21h | Domingo: 7h às 19h'
    }
  ],
  'Cordeirópolis': [
    {
      nome: 'TechFit Cordeirópolis',
      endereco: 'Rua XV de Novembro, 456 - Centro, Cordeirópolis - SP',
      telefone: '(19) 3546-7890',
      horario: 'Segunda a Sexta: 6h às 22h | Sábado: 7h às 19h | Domingo: 8h às 17h'
    }
  ],
  'Paulínia': [
    {
      nome: 'TechFit Paulínia Centro',
      endereco: 'Av. José Paulino, 123 - Centro, Paulínia - SP',
      telefone: '(19) 3874-1234',
      horario: 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'
    },
    {
      nome: 'TechFit Paulínia Betel',
      endereco: 'Rua dos Estudantes, 789 - Betel, Paulínia - SP',
      telefone: '(19) 3874-5678',
      horario: 'Segunda a Sexta: 6h às 23h | Sábado: 7h às 20h | Domingo: 8h às 18h'
    }
  ],
  'Iracemápolis': [
    {
      nome: 'TechFit Iracemápolis',
      endereco: 'Av. da República, 321 - Centro, Iracemápolis - SP',
      telefone: '(19) 3456-9012',
      horario: 'Segunda a Sexta: 6h às 22h | Sábado: 7h às 19h | Domingo: 8h às 17h'
    }
  ]
};

/**
 * Mostrar unidades de uma cidade
 */
function showUnits(cidade) {
  const cityFilterSection = document.getElementById('cityFilterSection');
  const unitsSection = document.getElementById('unitsSection');
  const selectedCity = document.getElementById('selectedCity');
  const unitsGrid = document.getElementById('unitsGrid');

  // Esconder filtro de cidade e mostrar unidades
  cityFilterSection.classList.add('hidden');
  unitsSection.classList.add('show');
  selectedCity.textContent = cidade;

  // Limpar grid anterior
  unitsGrid.innerHTML = '';

  // Adicionar unidades da cidade selecionada
  const unidades = unidadesData[cidade];
  unidades.forEach(unidade => {
    const unitCard = document.createElement('div');
    unitCard.className = 'unit-card';
    unitCard.innerHTML = `
      <div class="unit-name">
        <i class="fas fa-dumbbell"></i>
        <span>${unidade.nome}</span>
      </div>
      <div class="unit-info">
        <div class="unit-info-item">
          <i class="fas fa-map-pin"></i>
          <span class="unit-address">${unidade.endereco}</span>
        </div>
        <div class="unit-info-item">
          <i class="fas fa-phone"></i>
          <span class="unit-phone"><strong>Telefone:</strong> ${unidade.telefone}</span>
        </div>
        <div class="unit-info-item">
          <i class="fas fa-clock"></i>
          <span class="unit-hours"><strong>Horário:</strong> ${unidade.horario}</span>
        </div>
      </div>
    `;
    unitsGrid.appendChild(unitCard);
  });
}

/**
 * Mostrar filtro de cidades
 */
function showCityFilter() {
  const cityFilterSection = document.getElementById('cityFilterSection');
  const unitsSection = document.getElementById('unitsSection');

  // Esconder unidades e mostrar filtro de cidade
  unitsSection.classList.remove('show');
  cityFilterSection.classList.remove('hidden');
}

