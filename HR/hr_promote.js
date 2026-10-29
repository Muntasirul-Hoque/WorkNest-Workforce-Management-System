// Phase 1 to Phase 2 (Show fields for Approve)
function showPhase2(promoID) {
    document.getElementById('hrPhase1_' + promoID).style.display = 'none';
    document.getElementById('hrPhase2_' + promoID).style.display = 'flex';
}

// Phase 2 to Phase 1 (Cancel - Return to initial buttons)
function showPhase1(promoID) {
    document.getElementById('hrPhase2_' + promoID).style.display = 'none';
    document.getElementById('hrPhase1_' + promoID).style.display = 'flex';
    
    // Clear the dropdown selection and remove the "required" attribute
    document.getElementById('hrRole_' + promoID).value = '';
    document.getElementById('hrRole_' + promoID).removeAttribute('required');
}