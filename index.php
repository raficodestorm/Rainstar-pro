<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RainStar Pharma</title>
  <style>
    /* Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    /* Background Video */
    video {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover; /* Ensures video covers the whole screen */
      z-index: -1; /* Puts video behind everything */
    }

    /* Center Container */
    .content {
      position: relative;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: #fff;
      background: rgba(0, 0, 0, 0.4); /* dark overlay for readability */
    }

    /* Logo */
    .content img {
      width: 150px;
      margin-bottom: 20px;
      animation: fadeInDown 1s ease-out;
    }

    /* Title */
    .content p {
      font-size: 2rem;
      font-weight: bold;
      letter-spacing: 2px;
      margin-bottom: 30px;
      animation: fadeIn 2s ease-in;
    }

    /* Start Button */
    .content a {
      text-decoration: none;
      background: #00c9a7;
      color: white;
      padding: 12px 30px;
      font-size: 1.2rem;
      font-weight: bold;
      border-radius: 50px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      animation: fadeInUp 1.5s ease-out;
    }

    .content a:hover {
      background: #009f87;
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <!-- Background Video -->
  <!-- <video autoplay muted loop>
    <source src="" type="video/mp4">
    Your browser does not support the video tag.
  </video> -->

  <!-- Centered Content -->
  <div class="content">
    <img src="images/rainstar.png" alt="RainStar Logo">
    <p>RainStar Pharma</p>
    <a href="pages/loginform.php">Start</a>
  </div>
</body>
</html>
