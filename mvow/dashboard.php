<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

require_once 'includes/header.php';
?>

<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
<p>This is your dashboard.</p>

<div class="recorder-container">
    <h2>Voice Recorder</h2>
    <div id="visualizer"></div>
    <button id="recordButton">Record</button>
    <button id="stopButton" disabled>Stop</button>

    <div id="recordingsList">
        <h3>Recordings</h3>
        <ul id="recordings">
        <?php
        $recordingsDir = 'recordings/';
        if (is_dir($recordingsDir)) {
            $files = scandir($recordingsDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'wav') {
                    echo '<li class="recording-item">';
                    echo '<a href="' . htmlspecialchars($recordingsDir . $file) . '" target="_blank">' 
                         . htmlspecialchars($file) . '</a>';
                    echo '<button onclick="deleteRecording(\'' . htmlspecialchars($file) . '\')">Delete</button>';
                    echo '</li>';
                }
            }
        }
        ?>
        </ul>
    </div>
</div>

<style>
.recorder-container {
    background-color: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 80%;
    max-width: 600px;
    margin: 20px auto;
    text-align: center;
}

button {
    padding: 12px 25px;
    margin: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

#recordButton { background-color: #4CAF50; color: white; }
#stopButton { background-color: #f44336; color: white; }

#recordingsList {
    margin-top: 20px;
    text-align: left;
}

.recording-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

#visualizer {
    width: 100%;
    height: 80px;
    background-color: #e0e0e0;
    margin-bottom: 20px;
    border-radius: 5px;
}
</style>

<script>
let mediaRecorder;
let audioChunks = [];
let audioContext;
let analyser;

const recordButton = document.getElementById('recordButton');
const stopButton = document.getElementById('stopButton');
const visualizer = document.getElementById('visualizer');

// Visualizer setup
const canvas = document.createElement('canvas');
const canvasCtx = canvas.getContext('2d');
visualizer.appendChild(canvas);

navigator.mediaDevices.getUserMedia({ audio: true })
.then(stream => {
    mediaRecorder = new MediaRecorder(stream);
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    analyser = audioContext.createAnalyser();
    const source = audioContext.createMediaStreamSource(stream);
    source.connect(analyser);
    analyser.fftSize = 256;

    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);

    function drawVisualizer() {
        requestAnimationFrame(drawVisualizer);
        analyser.getByteFrequencyData(dataArray);

        canvas.width = visualizer.offsetWidth;
        canvas.height = visualizer.offsetHeight;

        canvasCtx.fillStyle = 'rgb(200, 200, 200)';
        canvasCtx.fillRect(0, 0, canvas.width, canvas.height);

        const barWidth = (canvas.width / bufferLength) * 2.5;
        let x = 0;

        for (let i = 0; i < bufferLength; i++) {
            const barHeight = dataArray[i] / 2;
            canvasCtx.fillStyle = `rgb(${barHeight + 100},50,50)`;
            canvasCtx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
            x += barWidth + 1;
        }
    }

    drawVisualizer();

    mediaRecorder.ondataavailable = event => {
        audioChunks.push(event.data);
    };

    mediaRecorder.onstop = async () => {
        const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
        const formData = new FormData();
        formData.append('audio', audioBlob, `recording_${Date.now()}.wav`);

        try {
            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData
            });
            if (response.ok) location.reload();
        } catch (error) {
            console.error('Upload error:', error);
        }
        audioChunks = [];
    };

    recordButton.onclick = () => {
        mediaRecorder.start();
        recordButton.disabled = true;
        stopButton.disabled = false;
    };

    stopButton.onclick = () => {
        mediaRecorder.stop();
        recordButton.disabled = false;
        stopButton.disabled = true;
    };
})
.catch(error => console.error('Microphone access error:', error));

async function deleteRecording(filename) {
    try {
        const response = await fetch(`delete.php?file=${encodeURIComponent(filename)}`);
        if (response.ok) location.reload();
    } catch (error) {
        console.error('Delete error:', error);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>