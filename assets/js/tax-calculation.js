function calculateTaxes() {
    const revenue = parseFloat(document.getElementById('revenue').value);
    const expenses = parseFloat(document.getElementById('expenses').value);
    const vatExemptions = parseFloat(document.getElementById('vat').value) || 0;

    const CIT_RATE = 0.25;
    const VAT_RATE = 0.125;
    const NHIL_RATE = 0.025;
    const GETFL_RATE = 0.025;
    const COVID_RATE = 0.01;

    const netIncome = revenue - expenses;
    const corporateIncomeTax = netIncome * CIT_RATE;
    const vat = (revenue - vatExemptions) * VAT_RATE;
    const nhil = revenue * NHIL_RATE;
    const getfl = revenue * GETFL_RATE;
    const covidTax = revenue * COVID_RATE;

    const taxSummary = `
        <ul>
            <li><strong>Corporate Income Tax (CIT):</strong> GHS ${corporateIncomeTax.toFixed(2)}</li>
            <li><strong>Value Added Tax (VAT):</strong> GHS ${vat.toFixed(2)}</li>
            <li><strong>National Health Insurance Levy (NHIL):</strong> GHS ${nhil.toFixed(2)}</li>
            <li><strong>Ghana Education Trust Fund Levy (GETFL):</strong> GHS ${getfl.toFixed(2)}</li>
            <li><strong>COVID-19 Health Recovery Levy:</strong> GHS ${covidTax.toFixed(2)}</li>
        </ul>
    `;
    document.getElementById('tax-summary').innerHTML = taxSummary;
}

function saveSummary() {
    const taxSummary = document.getElementById('tax-summary').innerHTML;
    if (taxSummary) {
        const savedSummaries = document.getElementById('saved-summaries');
        const newSummary = document.createElement('div');
        newSummary.classList.add('saved-summary');
        newSummary.innerHTML = taxSummary;
        savedSummaries.appendChild(newSummary);
    } else {
        alert('Please calculate the taxes before saving.');
    }
}
