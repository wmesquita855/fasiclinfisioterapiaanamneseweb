const ID_PROFISSIO = 1; // Código do fisioterapeuta (você)

// Carregar agendas desse fisioterapeuta
async function carregarAgenda() {
  try {
    const res = await fetch('http://localhost/appfisio/agenda.php?idProf=' + ID_PROFISSIO);
    const dados = await res.json();

    const lista = document.getElementById('listaAgenda');
    lista.innerHTML = '';

    if (dados.length === 0) {
      lista.innerHTML = '<li>Nenhum paciente agendado.</li>';
    } else {
      dados.forEach(item => {
        const li = document.createElement('li');
        li.textContent = `Paciente: ${item.ID_PESSOAFIS}, Procedimento: ${item.ID_PROCED}, Data: ${item.DATAABERT}`;
        lista.appendChild(li);
      });
    }
  } catch (error) {
    console.error("Erro ao carregar agenda:", error);
  }
}

// Inserir encaixe para você (ID_PROFISSIO = 1)
async function inserirEncaixe(e) {
  e.preventDefault();
  const body = {
    ID_PESSOAFIS: document.getElementById('idPessoa').value,
    ID_PROFISSIO: ID_PROFISSIO, // Sempre será 1
    ID_PROCED: document.getElementById('idProced').value,
    DATAABERT: document.getElementById('dataAgenda').value
  };

  try {
    const res = await fetch('http://localhost/appfisio/agenda.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    if (res.ok) {
      carregarAgenda();
      e.target.reset();
      document.getElementById('formAgenda').style.display = 'none';
    } else {
      alert("Erro ao salvar encaixe.");
    }
  } catch (error) {
    console.error("Erro ao inserir encaixe:", error);
  }
}
