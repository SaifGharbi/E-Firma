# ai_model/train_model.py

import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
import pickle

# Load the dataset with the correct file name
data = pd.read_csv('Crop_Recommendation.csv')

# Display the first few rows to verify data
print(data.head())

# Prepare the data
X = data[['temperature', 'humidity', 'ph', 'rainfall']]
y = data['label']

# Split the dataset into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train a RandomForest model
model = RandomForestClassifier()
model.fit(X_train, y_train)

# Evaluate the model accuracy
accuracy = model.score(X_test, y_test)
print(f'Model Accuracy: {accuracy * 100:.2f}%')

# Save the trained model to a file
with open('crop_recommendation_model.pkl', 'wb') as f:
    pickle.dump(model, f)

print('Model trained and saved as crop_recommendation_model.pkl')
