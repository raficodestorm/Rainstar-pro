    
    .form-container {
      background: #1a1f2e;
      border-radius: 14px;
      padding: 30px 20px;
      max-width: 1000px;
      width: 100%;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.05);
      box-sizing: border-box;
      margin: auto;
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #ffffff;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 20px;
    }

    label {
      margin-bottom: 8px;
      font-weight: 600;
      color: #bdbdbd;
    }

    input, select {
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #333;
      font-size: 15px;
      background-color: rgba(40, 40, 40, 0.9);
      color: #ffffff;
      transition: all 0.3s ease;
      width: 100%; /* full width inside container */
      box-sizing: border-box;
    }

    input:focus, select:focus {
      border-color: #4dabf7;
      outline: none;
      box-shadow: 0 0 8px rgba(77, 171, 247, 0.5);
      background-color: rgba(50, 50, 50, 0.95);
    }

    .section-title {
      margin: 20px 0 10px;
      font-size: 18px;
      color: #bbbbbb;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 5px;
    }

    /* Header for return items */
    #purchase-items-header {
      display: grid;
      grid-template-columns: 1.5fr 1.5fr 1.8fr 1fr 1fr 1fr 1fr 50px;
      gap: 20px;
      margin-bottom: 8px;
      font-weight: 600;
      color: #ccc;
      user-select: none;
      align-items: center;
    }
    #purchase-items-header div{
      text-align: center;
    }

    /* Container for all return item rows */
    #purchase-items-container {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 10px;
    }

    /* Each return item row is a grid with 6 columns */
    .purchase-item-row {
      display: grid;
      grid-template-columns: 1.5fr 1.5fr 1.5fr 1fr 1fr 1fr 1fr 50px;
      gap: 15px;
      align-items: center;
    }

    /* Remove margin bottom inside form-groups of return items */
    #purchase-items-container .form-group {
      margin-bottom: 0;
    }

    .add-medicine-btn {
      margin: 10px 0 30px;
      padding: 10px 18px;
      border: none;
      background: linear-gradient(135deg, #4dabf7, #339af0);
      color: white;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-block;
    }

    .add-medicine-btn:hover {
      background: linear-gradient(135deg, #74c0fc, #4dabf7);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(77, 171, 247, 0.4);
    }

    .submit-btn {
      background: linear-gradient(135deg, #4dabf7, #1c7ed6);
      color: white;
      padding: 12px 20px;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      width: 100%;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .submit-btn:hover {
      background: linear-gradient(135deg, #74c0fc, #4dabf7);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(77, 171, 247, 0.4);
    }

    .delete-btn {
      padding: 8px 10px;
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s ease;
      width: 40px;
      height: 40px;
      line-height: 1;
    }

    .delete-btn:hover {
      background: linear-gradient(135deg, #ff6b6b, #d63031);
      transform: scale(1.1);
    }

    /* Existing styles above */

/* Responsive adjustments */
@media (max-width: 720px) {
  #purchase-items-header,
  .purchase-item-row {
    grid-template-columns: 1fr; /* single column layout */
    gap: 15px;
  }

  /* Label and input stack vertically */
  .purchase-item-row .form-group {
    width: 100%;
  }

  /* Align labels properly */
  #purchase-items-header > div {
    display: none; /* hide the header grid labels on mobile, optional */
  }
}

@media (max-width: 480px) {
  input, select, textarea {
    font-size: 16px;
    padding: 14px;
  }

  .add-btn, .submit-btn {
    width: 100%;
    font-size: 18px;
    padding: 14px 0;
  }

  .delete-btn {
    width: 48px;
    height: 48px;
  }
}

    