pipeline {
    agent any

    environment {
        REMOTE_HOST = "172.23.215.51"
        REMOTE_USER = "jenkins"
        APP_DIR = "/opt"
        GIT_REPO = "git@github.com:Didier-Admin123/hello-world.git"
        GIT_BRANCH = "ci-cd-pipeline"
        IMAGE_NAME = "didierdorcelus1/nodejs"
        IMAGE_TAG = "${BUILD_NUMBER}"
        DOCKER_CREDENTIALS = "docker_cred"
    }

    stages {
        stage('Clone Repository on Jenkins') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        export GIT_SSH_COMMAND='ssh -o StrictHostKeyChecking=no'
                        rm -rf hello-world || true
                        git clone -b ${GIT_BRANCH} --single-branch ${GIT_REPO} hello-world
                    """
                }
            }
        }        
        
        stage('Deliver Artifacts to Build Server') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        scp -o StrictHostKeyChecking=no -r ${WORKSPACE}/hello-world ${REMOTE_USER}@${REMOTE_HOST}:${APP_DIR}
                    """
                }
            }
        }

        // SonarQube analyzes the code for vulnerabilities, security risks, and bad coding practices
        stage('SonarQube Code Analysis') {
            steps {
                withSonarQubeEnv('SonarQube') {
                    dir('hello-world') {
                        sh """
                            sonar-scanner \
                            -Dsonar.projectKey=hello-world \
                            -Dsonar.sources=. \
                            -Dsonar.host.url=http://sonarqube.local:9000 \
                            -Dsonar.login=${SONARQUBE_TOKEN}
                        """
                    }
                }
            }
        }

        // If SonarQube detects major issues, this step will stop the pipeline from continuing
        stage('Check SonarQube Quality Gate') {
            steps {
                timeout(time: 5, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }
        

    post {
        success {
            echo "✅ Deployment successful!"
        }
        failure {
            echo "❌ Deployment failed. Check logs."
        }
    }
}
