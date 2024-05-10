<?php
session_start();
if (isset($_SESSION["usuario"]) && is_array($_SESSION["usuario"])) {
    if ($_SESSION["usuario"][2] < 0) {
        header("location: login.php");
    } else {
        require("php/conexao.php");
        $conexaoClass = new Conexao();
        $conn = $conexaoClass->conectar();
    }
} else {
    header("location: login.php");
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <link rel="stylesheet" href="./Css/style.css" />
    <link rel="stylesheet" href="./Css/playlist.css" />
    <style>
        .audio-progress {
            width: 200px;
            color: #7d2ae8;
        }

        .volume-control {
            margin-left: 20px;
        }
    </style>
    <title>Spotify</title>
</head>

<body>
    <header>
        <div class="menu_side">
            <img src="Img/mkdg-girl-logo.png" class="image" alt="logo">
            <?php require("menu.php"); ?>
        </div>

        <div class="song_side">

            <?php
            // Pegando o id do artista que tá sendo passado pela URL
            $idArt = $_GET["idArt"];

            $selectArtista = $conn->prepare("SELECT * from artistas where id_artista = '$idArt'");
            $selectArtista->execute();
            $artista = $selectArtista->fetch(PDO::FETCH_ASSOC);
            ?>
            <div id="play">
                <div id="compositor">
                    <div class="imagem"><img src="data/imgs/<?= $artista['link_foto'] ?>"></div>
                    <!--                    <img src=" data/imgs/codigophpaqui alt="">-->
                    <span class="nomeComp"><?= $artista["nome"]; ?></span>
                </div>
                <?php
                $select_songs = $conn->prepare("SELECT * FROM `musicas` WHERE id_artista='$idArt'");
                $select_songs->execute();
                if ($select_songs->rowCount() > 0) {
                    while ($fetch_song = $select_songs->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <div id="musicas">
                            <div class="titulos"><?= $fetch_song["nome"]; ?></div>
                            <audio class="audio" id="audio_<?= $fetch_song['id_musica']; ?>">
                                <source src="data/<?= $fetch_song['link_arquivo']; ?>" type="audio/mpeg">
                            </audio>
                            <div id="player-controls">
                                <button class="play-btn" data-audio-id="<?= $fetch_song['id_musica']; ?>">Play</button>
                                <input type="range" class="audio-progress" value="0" max="100" step="0.01">
                                <span class="audio-duration">0:00</span>
                                <input type="range" class="volume-control" value="100" max="100" step="1">
                            </div>
                        </div>
                    <?php } ?>
            </div>

        </div>
    </header>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const PlayButtons = document.querySelectorAll('.play-btn');
            const ProgressBars = document.querySelectorAll('.audio-progress');
            const durationSpans = document.querySelectorAll('.audio-duration');

            PlayButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const audioId = button.getAttribute('data-audio-id');
                    const currentAudio = document.getElementById(`audio_${audioId}`);

                    // Pausa todas as outras músicas
                    document.querySelectorAll('.audio').forEach(audio => {
                        if (audio !== currentAudio) {
                            audio.pause();
                        }
                    });

                    // Inicia ou pausa a música clicada
                    if (currentAudio.paused) {
                        currentAudio.play();
                        button.textContent = "Pause";
                    } else {
                        currentAudio.pause();
                        button.textContent = 'Play';
                    }
                });
            });

            // Atualiza a barra de progresso e a duração da música durante a reprodução
            ProgressBars.forEach((progressBar, index) => {
                progressBar.addEventListener('input', () => {
                    const audioId = progressBar.previousElementSibling.getAttribute('data-audio-id');
                    const currentAudio = document.getElementById(`audio_${audioId}`);
                    const value = progressBar.value;
                    const duration = currentAudio.duration;

                    currentAudio.currentTime = (value / 100) * duration;
                });

                const audioId = progressBar.previousElementSibling.getAttribute('data-audio-id');
                const currentAudio = document.getElementById(`audio_${audioId}`);
                currentAudio.addEventListener('timeupdate', () => {
                    const durationMinutes = Math.floor(currentAudio.currentTime / 60);
                    const durationSeconds = Math.floor(currentAudio.currentTime % 60);
                    durationSpans[index].textContent = `${durationMinutes}:${durationSeconds < 10 ? '0' : ''}${durationSeconds}`;

                    const progress = (currentAudio.currentTime / currentAudio.duration) * 100;
                    progressBar.value = progress;
                });

                //controle de volume//
                const VolumeControls = document.querySelectorAll('.volume-control');
                VolumeControls.forEach(volumeControl => {
                    volumeControl.addEventListener('input', () => {
                        const audioId = volumeControl.parentElement.querySelector('.play-btn').getAttribute('data-audio-id');
                        const currentAudio = document.getElementById(`audio_${audioId}`);
                        const volume = volumeControl.value / 100;
                        currentAudio.volume = volume;
                    });
                });
            });
        });
    </script>
    <div class="right">
        <a href="php/logout.php" class="logout-button">Sair</a>
    </div>
    <!-- <script type="text/javascript" src="./Js/app.js"></script> -->
    <!-- <script type="text/javascript" src="./Js/music.js"></script> -->
    <!-- <script type="text/javascript" src="./Js/script.js"></script> -->
    <!-- <script type="text/javascript" src="./Js/home.js"></script> -->


</body>

</html>
<?php } ?>