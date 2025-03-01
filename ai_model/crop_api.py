# ai_model/crop_api.py

from flask import Flask, request, jsonify
import pickle

app = Flask(__name__)

# Load the trained model
model = pickle.load(open('crop_recommendation_model.pkl', 'rb'))

@app.route('/predict_crop', methods=['GET', 'POST'])
def predict_crop():
    if request.method == 'GET':
        return "API is running. Send a POST request with JSON data."
    
    data = request.json
    if not all(k in data for k in ('temperature', 'humidity', 'ph', 'rainfall')):
        return jsonify({'error': 'Missing required parameters'}), 400
    
    try:
        prediction = model.predict([[data['temperature'], data['humidity'], data['ph'], data['rainfall']]])
        return jsonify({'recommended_crop': prediction[0]})
    except Exception as e:
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    app.run(port=5000, debug=True)
