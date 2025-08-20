<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RainStar Pharma</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #0d1117, #1a1f2b);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
      overflow: hidden;
      transition: background 1s ease; /* smooth transition */
    }

    body::before {
      content: "";
      position: absolute;
      width: 200%;
      height: 200%;
      background: transparent url("https://www.transparenttextures.com/patterns/stardust.png") repeat;
      animation: floatStars 60s linear infinite;
      opacity: 0.3;
    }

    .content {
      text-align: center;
      z-index: 2;
    }

    .content img {
      width: 140px;
      margin-bottom: 20px;
      opacity: 0;
      animation: fadeInDown 1.2s ease-out forwards;
      filter: drop-shadow(0 0 12px rgba(0, 255, 200, 0.6));
    }

    .content p {
      font-size: 2.3rem;
      font-weight: bold;
      margin-bottom: 50px;
      text-transform: uppercase;
      letter-spacing: 3px;
      background: linear-gradient(90deg, #00f2fe, #4facfe);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      opacity: 0;
      animation: zoomIn 1.3s ease-out forwards, glowText 2s infinite alternate 0s;
    }

    .content a {
      position: relative;
      display: inline-block;
      padding: 14px 55px;
      font-size: 1.3rem;
      font-weight: bold;
      text-decoration: none;
      color: #fff;
      border-radius: 50px;
      text-transform: uppercase;
      letter-spacing: 2px;
      background: #111;
      overflow: hidden;
      transition: 0.3s;
      z-index: 1;
      box-shadow: 0 0 15px rgba(0, 255, 170, 0.6);
      opacity: 0;
      animation: fadeInUp 1.2s ease-out forwards;
    }

    .content a::before {
      content: "";
      position: absolute;
      inset: -3px;
      border-radius: 50px;
      padding: 3px;
      background: linear-gradient(90deg, #00f2fe, #4facfe, #00ffcc, #00f2fe);
      background-size: 300% 300%;
      animation: lightningMove 3s linear infinite;
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
              mask-composite: exclude;
      z-index: -1;
    }

    .content a::after {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 50px;
      background: radial-gradient(circle at 50% 50%, rgba(0, 255, 170, 0.2), transparent 80%);
      opacity: 0.6;
      z-index: -2;
      transition: 0.5s;
    }

    .content a:hover {
      color: #00f2fe;
      transform: scale(1.1);
      box-shadow: 0 0 25px rgba(0, 242, 254, 0.8),
                  0 0 60px rgba(0, 255, 170, 0.7);
      text-shadow: 0 0 12px #00f2fe, 0 0 20px #4facfe;
    }

    .content a:hover::after {
      opacity: 1;
      background: radial-gradient(circle at 50% 50%, rgba(0, 255, 170, 0.4), transparent 90%);
    }

    .content a span {
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: conic-gradient(
        from 0deg,
        transparent 0deg,
        rgba(0, 255, 200, 0.6) 90deg,
        transparent 180deg
      );
      transform: rotate(0deg);
      animation: sparkFlash 2s linear infinite;
      opacity: 0;
      border-radius: 50%;
      pointer-events: none;
    }

    .content a:hover span {
      opacity: 1;
      animation: sparkFlash 1s linear infinite;
    }

    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes zoomIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    @keyframes glowText { from { text-shadow: 0 0 5px #00f2fe, 0 0 10px #4facfe; } to { text-shadow: 0 0 25px #00f2fe, 0 0 45px #4facfe; } }
    @keyframes lightningMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    @keyframes sparkFlash { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes floatStars { from { transform: translateY(0); } to { transform: translateY(-500px); } }
  </style>
</head>
<body>
  <div class="content">
    <img src="images/rainstar.png" alt="RainStar Logo">
    <p>RainStar Pharma</p>
    <a href="pages/loginform.php">
      Start
      <span></span>
    </a>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script>
    $(document).ready(function(){
      const gradients = [
        "linear-gradient(135deg, #ff00cc, #333399)",
        "linear-gradient(135deg, #00f2fe, #4facfe)",
        "linear-gradient(135deg, #43e97b, #38f9d7)",
        "linear-gradient(135deg, #f7971e, #ffd200)",
        "linear-gradient(135deg,rgb(252, 70, 243), #3f5efb)",
        "linear-gradient(135deg, #00c6ff, #0072ff)"
      ];

      const defaultBg = "linear-gradient(135deg, #0d1117, #1a1f2b)";

      $(document).keydown(function(){
        const randomGradient = gradients[Math.floor(Math.random() * gradients.length)];
        $("body").css("background", randomGradient);
      });

      $(document).keyup(function(){
        $("body").css("background", defaultBg);
      });
    });
  </script>
</body>
</html>
